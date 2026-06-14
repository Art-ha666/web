<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Models\Article;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns recent trends into draft blog Articles using the configured AI provider,
 * with a keyless template fallback so it always produces something usable.
 */
class AiBlogWriter
{
    public function __construct(protected TrendSource $trends) {}

    /**
     * Resolve the active generator, falling back to the template generator when
     * the chosen provider has no API key configured.
     */
    public function generator(?string $provider = null): AiTextGenerator
    {
        // Explicit provider override (e.g. `blog:generate --provider=gemini`).
        if ($provider !== null) {
            return $this->fromProviderKey($provider);
        }

        // Otherwise prefer the admin's active, usable AI provider from the DB.
        $active = AiProvider::query()->where('is_active', true)->orderByDesc('id')->first();

        if ($active !== null && $active->isUsable()) {
            $driver = $active->provider === 'gemini'
                ? new GeminiGenerator($active->model, $active->api_key)
                : new OpenAiGenerator($active->model, $active->api_key);

            if ($driver->configured()) {
                return $driver;
            }
        }

        // Fall back to the env-configured provider chosen in settings.
        return $this->fromProviderKey(SiteSetting::current()->ai_provider ?: 'openai');
    }

    protected function fromProviderKey(string $provider): AiTextGenerator
    {
        $settings = SiteSetting::current();

        $driver = match ($provider) {
            'gemini' => new GeminiGenerator($settings->ai_gemini_model ?: null),
            'template' => new TemplateGenerator,
            default => new OpenAiGenerator($settings->ai_openai_model ?: null),
        };

        return $driver->configured() ? $driver : new TemplateGenerator;
    }

    /**
     * Generate up to $count draft articles.
     *
     * @return Collection<int, Article>
     */
    public function generate(int $count = 1, ?string $provider = null): Collection
    {
        $generator = $this->generator($provider);
        $topics = $this->trends->recent(max($count * 2, 4));
        $author = TeamMember::query()->where('is_active', true)->where('featured', true)->first()
            ?? TeamMember::query()->where('is_active', true)->first();

        $created = collect();

        foreach (array_slice($topics, 0, $count) as $topic) {
            try {
                [$system, $user] = $this->prompt($topic, $topics);
                $data = $this->parse($generator->generate($system, $user));
            } catch (Throwable $e) {
                report($e);

                continue;
            }

            if ($data === null) {
                continue;
            }

            $article = $this->toArticle($data, $author?->id, $generator->key());

            if ($article !== null) {
                $created->push($article);
            }
        }

        return $created;
    }

    /**
     * @param  array<int, string>  $topics
     * @return array{0: string, 1: string}
     */
    protected function prompt(string $topic, array $topics): array
    {
        $system = 'You are an experienced software engineer writing for the engineering blog of AKH Solutions, a software, AI, and IT engineering agency that builds across web, mobile, AI & data, embedded & robotics, IoT, desktop, networking, and cloud. '
            .'Write with authority and concrete detail, no marketing fluff, no first-person company hype. '
            .'Use plain hyphens ("-") for punctuation; never em dashes or en dashes. Return ONLY a single JSON object.';

        $user = "Write an original, useful blog post for the AKH Solutions engineering blog.\n"
            ."PRIMARY_TOPIC: {$topic}\n"
            .'Recent industry headlines to draw inspiration from: '.implode('; ', array_slice($topics, 0, 8)).".\n"
            .'Return a JSON object with EXACTLY these keys: '
            .'title (string, <=70 chars, specific and compelling), '
            .'excerpt (string, <=200 chars), '
            .'body_html (string, 600-900 words of valid HTML using only <h2>, <p>, <ul>, <li>, <strong>, <code>; never an <h1>), '
            .'tags (array of 2-4 short strings), '
            .'reading_time (integer minutes), '
            .'seo_title (string, <=60 chars), '
            .'seo_description (string, <=155 chars).';

        return [$system, $user];
    }

    /**
     * Defence-in-depth: AI output is rendered with v-html, so strip anything
     * that isn't a safe content tag and remove event handlers / javascript: URLs.
     */
    protected function sanitizeHtml(string $html): string
    {
        $html = strip_tags($html, '<h2><h3><h4><p><ul><ol><li><strong><em><b><i><code><pre><blockquote><a>');
        $html = (string) preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = (string) preg_replace('/javascript:/i', '', $html);

        return trim($html);
    }

    protected function parse(string $raw): ?array
    {
        $raw = trim($raw);
        $raw = (string) preg_replace('/^```[a-zA-Z]*|```$/m', '', $raw);

        $data = json_decode(trim($raw), true);

        if (! is_array($data) || empty($data['title'])) {
            return null;
        }

        // Site style: plain hyphens only. Models love em dashes, so normalize
        // every dash variant after decoding (covers \uXXXX-escaped payloads).
        array_walk_recursive($data, function (&$value): void {
            if (is_string($value)) {
                $value = str_replace(["\u{2014}", "\u{2013}", "\u{2012}", "\u{2015}", "\u{2212}"], '-', $value);
            }
        });

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function toArticle(array $data, ?int $authorId, string $source): ?Article
    {
        $slug = Str::slug((string) $data['title']);

        if ($slug === '' || Article::where('slug', $slug)->exists()) {
            return null; // dedupe - skip topics we already covered
        }

        $autopublish = (bool) SiteSetting::current()->ai_blog_autopublish;

        return Article::create([
            'title' => Str::limit((string) $data['title'], 150, ''),
            'slug' => $slug,
            'excerpt' => Str::limit((string) ($data['excerpt'] ?? ''), 290, ''),
            'body' => $this->sanitizeHtml((string) ($data['body_html'] ?? '<p></p>')),
            'tags' => array_slice(array_values(array_filter((array) ($data['tags'] ?? ['Engineering']))), 0, 4),
            'reading_time' => max(1, (int) ($data['reading_time'] ?? 6)),
            'author_id' => $authorId,
            'status' => $autopublish ? 'published' : 'draft',
            'featured' => false,
            'published_at' => $autopublish ? now() : null,
            'seo_title' => Str::limit((string) ($data['seo_title'] ?? $data['title']), 160, ''),
            'seo_description' => Str::limit((string) ($data['seo_description'] ?? ($data['excerpt'] ?? '')), 300, ''),
            'generated_by' => 'ai:'.$source,
        ]);
    }
}
