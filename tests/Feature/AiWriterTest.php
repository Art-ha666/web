<?php

use App\Models\AiProvider;
use App\Models\Article;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Ai\AiBlogWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // Never hit the network in tests; trend sources fall back to seed topics.
    Http::preventStrayRequests();
    Http::fake([
        'dev.to/*' => Http::response([], 200),
        'hn.algolia.com/*' => Http::response(['hits' => []], 200),
    ]);
});

it('shows the AI writer with provider status', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/ai-writer')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/AiWriter')
            ->has('keyStatus')
            ->has('aiProviders')
            ->has('settings')
        );
});

it('saves AI writer settings', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/ai-writer', [
            'ai_blog_enabled' => true,
            'ai_blog_frequency' => 'twice_weekly',
            'ai_blog_per_run' => 2,
            'ai_blog_topics' => 'Platform engineering, AI agents',
            'ai_blog_autopublish' => false,
        ])
        ->assertRedirect();

    $settings = SiteSetting::current();
    expect($settings->ai_blog_enabled)->toBeTrue();
    expect($settings->ai_blog_frequency)->toBe('twice_weekly');
    expect($settings->ai_blog_per_run)->toBe(2);
});

it('generates a draft article on demand (template fallback, no API key)', function () {
    config(['services.openai.key' => null, 'services.gemini.key' => null]);

    $this->actingAs(User::factory()->create())
        ->post('/admin/ai-writer/generate')
        ->assertRedirect();

    $article = Article::query()->whereNotNull('generated_by')->first();
    expect($article)->not->toBeNull();
    expect($article->status)->toBe('draft');
    expect($article->generated_by)->toBe('ai:template');
});

it('writes via the OpenAI provider when a key is configured', function () {
    config(['services.openai.key' => 'test-key', 'services.openai.model' => 'gpt-4o-mini']);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode([
                'title' => 'A Concrete Guide to Platform Engineering',
                'excerpt' => 'What golden paths really mean.',
                'body_html' => '<h2>Intro</h2><p>Body.</p>',
                'tags' => ['Platform', 'DevOps'],
                'reading_time' => 7,
                'seo_title' => 'Platform Engineering Guide',
                'seo_description' => 'Golden paths in practice.',
            ])]]],
        ], 200),
        'dev.to/*' => Http::response([], 200),
        'hn.algolia.com/*' => Http::response(['hits' => []], 200),
    ]);

    $created = app(AiBlogWriter::class)->generate(1, 'openai');

    expect($created)->toHaveCount(1);
    expect(Article::where('slug', 'a-concrete-guide-to-platform-engineering')->where('generated_by', 'ai:openai')->exists())->toBeTrue();
});

it('runs the blog:generate command', function () {
    config(['services.openai.key' => null, 'services.gemini.key' => null]);

    $this->artisan('blog:generate', ['--count' => 1, '--provider' => 'template'])
        ->assertSuccessful();

    expect(Article::query()->whereNotNull('generated_by')->count())->toBeGreaterThanOrEqual(1);
});

it('adds an AI provider with an encrypted key, activates and removes it', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)->post('/admin/ai-writer/providers', [
        'name' => 'My OpenAI', 'provider' => 'openai', 'model' => 'gpt-5.4', 'api_key' => 'sk-secret-123',
    ])->assertRedirect();

    $provider = AiProvider::where('name', 'My OpenAI')->first();
    expect($provider)->not->toBeNull();
    expect($provider->api_key)->toBe('sk-secret-123'); // decrypted via cast
    // Stored encrypted at rest - the raw DB value is NOT the plaintext key.
    expect(DB::table('ai_providers')->where('id', $provider->id)->value('api_key'))->not->toBe('sk-secret-123');

    $this->actingAs($admin)->put("/admin/ai-writer/providers/{$provider->id}/activate")->assertRedirect();
    expect($provider->fresh()->is_active)->toBeTrue();

    $this->actingAs($admin)->delete("/admin/ai-writer/providers/{$provider->id}")->assertRedirect();
    expect(AiProvider::find($provider->id))->toBeNull();
});

it('strips scripts and event handlers from AI-generated HTML', function () {
    config(['services.openai.key' => 'test-key']);

    Http::fake([
        'api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'title' => 'A Safe Post About Caching',
            'excerpt' => 'x',
            'body_html' => '<p>safe</p><script>alert(1)</script><p onclick="evil()">hi</p>',
            'tags' => ['x'], 'reading_time' => 5, 'seo_title' => 't', 'seo_description' => 'd',
        ])]]]], 200),
        'dev.to/*' => Http::response([], 200),
        'hn.algolia.com/*' => Http::response(['hits' => []], 200),
    ]);

    app(AiBlogWriter::class)->generate(1, 'openai');

    $body = Article::where('slug', 'a-safe-post-about-caching')->value('body');
    expect($body)->not->toContain('<script')->not->toContain('onclick');
});

it('normalizes em and en dashes to plain hyphens in AI output', function () {
    config(['services.openai.key' => 'test-key']);

    Http::fake([
        'api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'title' => "Queues \u{2014} a field guide",
            'excerpt' => "Backpressure \u{2013} explained",
            'body_html' => "<p>Retries \u{2014} with jitter \u{2013} beat thundering herds.</p>",
            'tags' => ["ops \u{2014} notes"], 'reading_time' => 5,
            'seo_title' => "Queues \u{2014} guide", 'seo_description' => "Dashes \u{2013} gone",
        ])]]]], 200),
        'dev.to/*' => Http::response([], 200),
        'hn.algolia.com/*' => Http::response(['hits' => []], 200),
    ]);

    app(AiBlogWriter::class)->generate(1, 'openai');

    $article = Article::where('slug', 'like', 'queues%')->firstOrFail();
    $haystack = $article->title.$article->excerpt.$article->body.$article->seo_title
        .$article->seo_description.implode(' ', $article->tags);

    expect($haystack)->not->toContain("\u{2014}")->not->toContain("\u{2013}")
        ->and($article->title)->toBe('Queues - a field guide');
});

it('generates using the active DB provider key and chosen model', function () {
    config(['services.openai.key' => null]);
    AiProvider::create(['name' => 'Active', 'provider' => 'openai', 'model' => 'gpt-5.4', 'api_key' => 'sk-db-key', 'is_active' => true]);

    Http::fake([
        'api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'title' => 'Edge Functions for Low-Latency Apps',
            'excerpt' => 'x', 'body_html' => '<p>x</p>', 'tags' => ['Edge'], 'reading_time' => 5,
            'seo_title' => 't', 'seo_description' => 'd',
        ])]]]], 200),
        'dev.to/*' => Http::response([], 200),
        'hn.algolia.com/*' => Http::response(['hits' => []], 200),
    ]);

    $created = app(AiBlogWriter::class)->generate(1);

    expect($created)->toHaveCount(1);
    Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'Bearer sk-db-key'));
});
