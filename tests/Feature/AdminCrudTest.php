<?php

use App\Models\Article;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates, updates and deletes a service', function () {
    $this->post('/admin/services', [
        'title' => 'Test Service',
        'icon' => 'sparkles',
        'benefit_bullets' => ['Ship fast'],
        'tech_stack' => ['Laravel'],
        'gradient' => ['from' => '#111111', 'via' => '#222222', 'to' => '#333333'],
        'is_active' => true,
        'featured' => false,
        'sort_order' => 0,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $service = Service::firstWhere('title', 'Test Service');
    expect($service)->not->toBeNull();
    expect($service->slug)->toBe('test-service');

    $this->put("/admin/services/{$service->slug}", [
        'title' => 'Renamed Service',
        'slug' => $service->slug,
        'is_active' => true,
        'featured' => true,
        'sort_order' => 1,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($service->fresh()->title)->toBe('Renamed Service');

    $this->delete("/admin/services/{$service->slug}")->assertRedirect();
    expect(Service::find($service->id))->toBeNull();
});

it('creates a case study', function () {
    $this->post('/admin/projects', [
        'title' => 'Test Project',
        'industry' => 'Fintech',
        'year' => '2025',
        'category_tags' => ['Fintech'],
        'tech_stack' => ['Go'],
        'results' => [['metric' => '10x', 'label' => 'faster']],
        'is_published' => true,
        'featured' => false,
        'sort_order' => 0,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('projects', ['title' => 'Test Project', 'slug' => 'test-project']);
});

it('creates a stat', function () {
    $this->post('/admin/stats', [
        'value' => '99',
        'suffix' => '%',
        'label' => 'Uptime',
        'group' => 'band',
        'is_active' => true,
        'sort_order' => 0,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('stats', ['label' => 'Uptime', 'value' => '99']);
});

it('creates an article with an author', function () {
    $author = TeamMember::factory()->create();

    $this->post('/admin/articles', [
        'title' => 'Test Article',
        'excerpt' => 'An engineering note.',
        'body' => '<p>Body</p>',
        'author_id' => $author->id,
        'tags' => ['AI'],
        'reading_time' => 5,
        'status' => 'published',
        'featured' => false,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Article::firstWhere('title', 'Test Article'))->not->toBeNull();
});

it('creates a team member', function () {
    $this->post('/admin/team', [
        'name' => 'Test Person',
        'role' => 'Engineer',
        'is_active' => true,
        'featured' => false,
        'sort_order' => 0,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('team_members', ['name' => 'Test Person']);
});
