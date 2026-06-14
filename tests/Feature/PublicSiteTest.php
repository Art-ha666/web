<?php

use App\Models\Article;
use App\Models\JobPosting;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the homepage', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('public/Home'));
});

it('renders the core public pages', function (string $url, string $component) {
    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['/services', 'public/Services'],
    ['/work', 'public/Work'],
    ['/process', 'public/Process'],
    ['/about', 'public/About'],
    ['/team', 'public/Team'],
    ['/insights', 'public/Insights'],
    ['/careers', 'public/Careers'],
    ['/contact', 'public/Contact'],
]);

it('shows a service detail page', function () {
    $service = Service::factory()->create(['is_active' => true]);

    $this->get('/services/'.$service->slug)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('public/ServiceShow'));
});

it('shows a case study detail page', function () {
    $project = Project::factory()->create(['is_published' => true]);

    $this->get('/work/'.$project->slug)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('public/WorkShow'));
});

it('shows a published article and 404s drafts', function () {
    $author = TeamMember::factory()->create();
    $published = Article::factory()->create(['status' => 'published', 'published_at' => now()->subDay(), 'author_id' => $author->id]);
    $draft = Article::factory()->create(['status' => 'draft', 'published_at' => null]);

    $this->get('/insights/'.$published->slug)->assertOk();
    $this->get('/insights/'.$draft->slug)->assertNotFound();
});

it('shows an open job and 404s closed ones', function () {
    $open = JobPosting::factory()->create(['is_open' => true]);
    $closed = JobPosting::factory()->create(['is_open' => false]);

    $this->get('/careers/'.$open->slug)->assertOk();
    $this->get('/careers/'.$closed->slug)->assertNotFound();
});

it('renders the legal pages', function (string $slug) {
    Page::factory()->create([
        'slug' => $slug,
        'status' => 'published',
        'blocks' => [['type' => 'richtext', 'data' => ['html' => '<p>Legal content.</p>']]],
    ]);

    $this->get('/'.$slug)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('public/Page'));
})->with(['privacy', 'cookies', 'terms']);

it('renders an admin-created page and 404s drafts', function () {
    Page::factory()->create(['slug' => 'our-promise', 'status' => 'published', 'blocks' => [['type' => 'richtext', 'data' => ['html' => '<p>Hi.</p>']]]]);
    Page::factory()->create(['slug' => 'secret-draft', 'status' => 'draft']);

    $this->get('/page/our-promise')->assertOk()->assertInertia(fn (Assert $page) => $page->component('public/Page'));
    $this->get('/page/secret-draft')->assertNotFound();
});
