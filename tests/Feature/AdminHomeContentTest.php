<?php

use App\Models\Page;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function seedHomePage(): Page
{
    return Page::factory()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_system' => true,
        'status' => 'published',
        'blocks' => [
            'hero' => ['headline_line1' => 'Old headline', 'trust_items' => ['a', 'b', 'c']],
            'partners' => ['enabled' => true, 'eyebrow' => 'Old partners'],
            'sections' => [
                'team' => ['enabled' => true, 'title' => 'Old team'],
                'closing' => ['enabled' => true],
            ],
        ],
    ]);
}

it('lets an admin open the homepage editor', function () {
    seedHomePage();

    $this->actingAs(User::factory()->create())
        ->get('/admin/home')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/Home')->has('sectionMeta'));
});

it('toggles a homepage section off and rewords the page', function () {
    seedHomePage();

    $this->actingAs(User::factory()->create())
        ->put('/admin/home', [
            'hero' => ['headline_line1' => 'New headline', 'trust_items' => ['x', '', 'z']],
            'partners' => ['enabled' => false, 'eyebrow' => 'New partners'],
            'sections' => [
                'team' => ['enabled' => false, 'eyebrow' => '', 'title' => 'New team', 'subtitle' => ''],
                'closing' => ['enabled' => true],
            ],
        ])
        ->assertRedirect();

    $blocks = Page::where('slug', 'home')->first()->blocks;

    expect($blocks['hero']['headline_line1'])->toBe('New headline');
    // Blank trust badges are dropped and the list is re-indexed.
    expect($blocks['hero']['trust_items'])->toBe(['x', 'z']);
    expect($blocks['partners']['enabled'])->toBeFalse();
    expect($blocks['partners']['eyebrow'])->toBe('New partners');
    expect($blocks['sections']['team']['enabled'])->toBeFalse();
    expect($blocks['sections']['team']['title'])->toBe('New team');
});

it('renders the homepage with a disabled section hidden', function () {
    Page::factory()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_system' => true,
        'status' => 'published',
        'blocks' => [
            'hero' => ['headline_line1' => 'Hi'],
            'partners' => ['enabled' => false],
            'sections' => ['team' => ['enabled' => false]],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Home')
            ->where('home.sections.team.enabled', false)
            ->where('home.partners.enabled', false)
        );
});
