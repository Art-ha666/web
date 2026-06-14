<?php

use App\Models\Article;
use App\Models\Page;
use App\Models\Service;
use Inertia\Testing\AssertableInertia as Assert;

it('serves an xml sitemap listing public urls', function () {
    Service::factory()->create(['slug' => 'custom-software', 'is_active' => true]);
    Article::factory()->create(['slug' => 'a-post', 'status' => 'published', 'published_at' => now()->subDay()]);

    $res = $this->get('/sitemap.xml')->assertOk();

    expect($res->getContent())->toContain('<urlset')
        ->toContain('/services/custom-software')
        ->toContain('/insights/a-post')
        ->toContain('/contact');
});

it('auto-includes published admin pages and excludes hidden ones from the sitemap', function () {
    Page::factory()->create(['slug' => 'our-promise', 'status' => 'published']);
    Page::factory()->create(['slug' => 'about', 'status' => 'draft']);

    $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($xml)->toContain('/page/our-promise')   // admin page auto-listed
        ->not->toContain('/about</loc>');          // hidden page excluded
});

it('renders substantial Terms content (not empty)', function () {
    Page::factory()->create([
        'slug' => 'terms',
        'status' => 'published',
        'blocks' => [['type' => 'richtext', 'data' => ['html' => '<p>These Terms govern your use of the site.</p><h2>Use</h2>']]],
    ]);

    $this->get('/terms')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Page')
            ->where('page.blocks.0.data.html', '<p>These Terms govern your use of the site.</p><h2>Use</h2>')
        );
});

it('exposes robots.txt that allows crawling and points to the sitemap', function () {
    $res = $this->get('/robots.txt')->assertOk();
    expect($res->getContent())->toContain('Sitemap:')->toContain('Disallow: /admin');
});

it('server-renders article meta, og tags, and BlogPosting JSON-LD for crawlers', function () {
    $article = Article::factory()->create([
        'title' => 'Queues That Survive Black Friday',
        'slug' => 'queues-that-survive-black-friday',
        'seo_title' => 'Queues that survive Black Friday - AKH Solutions',
        'seo_description' => 'Designing work queues that absorb 50x traffic spikes.',
        'status' => 'published',
    ]);

    $html = $this->get("/insights/{$article->slug}")->assertOk()->getContent();

    expect($html)
        ->toContain('<meta property="og:title" content="Queues that survive Black Friday - AKH Solutions">')
        ->toContain('<meta property="og:description" content="Designing work queues that absorb 50x traffic spikes.">')
        ->toContain('<meta property="og:type" content="article">')
        ->toContain('article:published_time')
        ->toContain('"@type": "BlogPosting"')
        ->toContain('"headline": "Queues That Survive Black Friday"');
});

it('server-renders per-page meta from the page content schema on listing pages', function () {
    $html = $this->get('/team')->assertOk()->getContent();

    expect($html)
        ->toContain('<meta property="og:title" content="The team - AKH Solutions">')
        ->toContain('<meta name="robots" content="index, follow, max-image-preview:large">');
});

it('server-renders service meta with the blurb as description fallback', function () {
    $service = Service::factory()->create([
        'title' => 'Firmware Rescue',
        'slug' => 'firmware-rescue',
        'seo_title' => null,
        'seo_description' => null,
        'short_blurb' => 'We take over firmware no one else will touch.',
        'is_active' => true,
    ]);

    $html = $this->get("/services/{$service->slug}")->assertOk()->getContent();

    expect($html)
        ->toContain('<meta property="og:title" content="Firmware Rescue">')
        ->toContain('<meta property="og:description" content="We take over firmware no one else will touch.">');
});

it('marks the login page noindex while public pages stay indexable', function () {
    expect($this->get('/login')->assertOk()->getContent())
        ->toContain('<meta name="robots" content="noindex, nofollow">');

    expect($this->get('/')->assertOk()->getContent())
        ->toContain('<meta name="robots" content="index, follow, max-image-preview:large">');
});

it('points og:image at the bundled 1200x630 default image', function () {
    expect(file_exists(public_path('og-default.png')))->toBeTrue();

    [$width, $height] = getimagesize(public_path('og-default.png'));
    expect($width)->toBe(1200)->and($height)->toBe(630);

    expect($this->get('/')->getContent())
        ->toContain('<meta property="og:image" content="'.url('/og-default.png').'">')
        ->toContain('<meta property="og:image:width" content="1200">');
});

it('emits BreadcrumbList JSON-LD on nested CMS pages', function () {
    $parent = Page::factory()->create(['slug' => 'solutions', 'title' => 'Solutions', 'status' => 'published', 'published_at' => now()]);
    $child = Page::factory()->create([
        'slug' => 'iot-fleet',
        'title' => 'IoT Fleet',
        'status' => 'published',
        'published_at' => now(),
        'parent_id' => $parent->id,
    ]);

    $html = $this->get('/page/solutions/iot-fleet')->assertOk()->getContent();

    expect($html)
        ->toContain('"@type": "BreadcrumbList"')
        ->toContain('"name": "Solutions"')
        ->toContain('"name": "IoT Fleet"');
});
