<?php

use App\Models\Page;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders a nested page at its full slug path with breadcrumbs', function () {
    $parent = Page::factory()->create(['slug' => 'solutions', 'title' => 'Solutions', 'status' => 'published']);
    $child = Page::factory()->create(['slug' => 'robotics', 'title' => 'Robotics', 'status' => 'published', 'parent_id' => $parent->id]);

    $this->get('/page/solutions/robotics')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Page')
            ->where('page.slug', 'robotics')
            ->where('breadcrumbs.0.title', 'Solutions')
            ->where('breadcrumbs.0.url', '/page/solutions'));

    expect($child->publicPath())->toBe('/page/solutions/robotics');
});

it('redirects a stale flat path to the canonical nested path', function () {
    $parent = Page::factory()->create(['slug' => 'solutions', 'status' => 'published']);
    Page::factory()->create(['slug' => 'robotics', 'status' => 'published', 'parent_id' => $parent->id]);

    $this->get('/page/robotics')->assertRedirect('/page/solutions/robotics');
});

it('redirects /page/{slug} duplicates of top-level routes to their canonical url', function () {
    Page::factory()->create(['slug' => 'careers', 'status' => 'published', 'is_system' => true]);
    Page::factory()->create(['slug' => 'privacy', 'status' => 'published', 'is_system' => true]);

    $this->get('/page/careers')->assertRedirect('/careers');
    $this->get('/page/privacy')->assertRedirect('/privacy');
});

it('does not list /page duplicates of top-level routes in the sitemap', function () {
    Page::factory()->create(['slug' => 'careers', 'status' => 'published', 'is_system' => true]);
    Page::factory()->create(['slug' => 'home', 'status' => 'published', 'is_system' => true]);

    $response = $this->get('/sitemap.xml')->assertOk();

    expect($response->getContent())
        ->not->toContain('/page/careers')
        ->not->toContain('/page/home');
});

it('lets an admin nest a page under a parent', function () {
    $this->actingAs(User::factory()->create());

    $parent = Page::factory()->create(['slug' => 'solutions', 'status' => 'published']);

    $this->post('/admin/pages', [
        'title' => 'IoT Fleet Monitoring',
        'parent_id' => $parent->id,
        'status' => 'published',
        'blocks' => [],
    ])->assertRedirect('/admin/pages');

    $child = Page::query()->where('slug', 'iot-fleet-monitoring')->firstOrFail();

    expect($child->parent_id)->toBe($parent->id)
        ->and($child->publicPath())->toBe('/page/solutions/iot-fleet-monitoring');
});

it('rejects nesting a page under its own descendant', function () {
    $this->actingAs(User::factory()->create());

    $parent = Page::factory()->create(['slug' => 'parent-page', 'status' => 'published']);
    $child = Page::factory()->create(['slug' => 'child-page', 'status' => 'published', 'parent_id' => $parent->id]);

    $this->put("/admin/pages/{$parent->slug}", [
        'title' => $parent->title,
        'slug' => $parent->slug,
        'parent_id' => $child->id,
        'status' => 'published',
        'blocks' => [],
    ])->assertStatus(422);
});

it('rejects creating a page with a reserved slug', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/admin/pages', [
        'title' => 'Sneaky',
        'slug' => 'services',
        'status' => 'published',
        'blocks' => [],
    ])->assertSessionHasErrors('slug');
});

it('deletes a non-system page from the admin index', function () {
    $this->actingAs(User::factory()->create());

    $page = Page::factory()->create(['slug' => 'deletable', 'is_system' => false]);

    $this->delete("/admin/pages/{$page->slug}")->assertRedirect('/admin/pages');

    expect(Page::query()->where('slug', 'deletable')->exists())->toBeFalse();
});
