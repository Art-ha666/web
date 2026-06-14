<?php

namespace App\Services\Ai;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Pulls recent software/tech trend headlines to ground AI blog drafts.
 * Falls back to admin-defined topics, then a curated default list, so it never
 * fails even with no network.
 */
class TrendSource
{
    public function recent(int $limit = 6): array
    {
        $trends = Cache::remember('ai.trends', now()->addHours(6), fn (): array => $this->fetch());

        if (count($trends) < 3) {
            $trends = array_merge($trends, $this->fallbackTopics());
        }

        return array_slice(array_values(array_unique($trends)), 0, max($limit, 3));
    }

    protected function fetch(): array
    {
        $titles = [];

        try {
            $dev = Http::timeout(8)->retry(2, 300, throw: false)->acceptJson()
                ->get('https://dev.to/api/articles', ['per_page' => 12, 'top' => 7])
                ->json();
            foreach ((array) $dev as $article) {
                if (! empty($article['title'])) {
                    $titles[] = trim((string) $article['title']);
                }
            }
        } catch (Throwable) {
            // ignore - fall through to other sources
        }

        try {
            $hits = Http::timeout(8)->retry(2, 300, throw: false)->acceptJson()
                ->get('https://hn.algolia.com/api/v1/search', ['tags' => 'front_page'])
                ->json('hits', []);
            foreach ((array) $hits as $hit) {
                if (! empty($hit['title'])) {
                    $titles[] = trim((string) $hit['title']);
                }
            }
        } catch (Throwable) {
            // ignore
        }

        return array_values(array_filter(array_unique($titles)));
    }

    /**
     * @return array<int, string>
     */
    public function fallbackTopics(): array
    {
        $custom = collect(preg_split('/[\n,]+/', (string) SiteSetting::current()->ai_blog_topics))
            ->map(fn ($t): string => trim((string) $t))
            ->filter()
            ->values()
            ->all();

        if ($custom !== []) {
            return $custom;
        }

        return [
            'Building reliable AI agents in production',
            'Platform engineering and golden paths',
            'Zero-downtime database migrations',
            'Cutting cloud costs without cutting corners',
            'Designing systems for 99.9% uptime',
            'Releasing LLM features safely',
            'Event-driven architecture in practice',
            'Type-safe full-stack with Laravel and Vue',
        ];
    }
}
