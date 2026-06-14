<?php

use App\Models\Page;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists editable pages in the content index', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/content')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/content/Index')
            ->has('pages', fn (Assert $p) => $p->where('0.slug', 'about')->etc())
        );
});

it('opens the editor for a page with its schema and merged content', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/content/about')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/content/Edit')
            ->where('slug', 'about')
            ->has('sections')
            ->where('content.hero.title', 'Engineers for the')
        );
});

it('404s an unknown page slug', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/content/does-not-exist')
        ->assertNotFound();
});

it('saves page content overrides and reflects them on the live page', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/content/about', [
            'content' => [
                'hero' => ['eyebrow' => 'Our story', 'title' => 'A brand new headline', 'title_accent' => 'edited.', 'body' => 'New intro.'],
                'principles' => ['enabled' => false],
            ],
        ])
        ->assertRedirect();

    $blocks = Page::where('slug', 'about')->first()->blocks;
    expect($blocks['hero']['title'])->toBe('A brand new headline');
    expect($blocks['principles']['enabled'])->toBeFalse();

    // The public page now serves the edited copy.
    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/About')
            ->where('content.hero.title', 'A brand new headline')
            ->where('content.principles.enabled', false)
        );
});

it('resets a page back to the original wording', function () {
    Page::factory()->create([
        'slug' => 'about',
        'is_system' => true,
        'status' => 'published',
        'blocks' => ['hero' => ['title' => 'Edited away from default']],
    ]);

    $this->actingAs(User::factory()->create())
        ->put('/admin/content/about/reset')
        ->assertRedirect();

    $blocks = Page::where('slug', 'about')->first()->blocks;
    expect($blocks['hero']['title'])->toBe('Engineers for the');
});

it('hides a page (404) when set to draft and shows it again when live', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)->put('/admin/content/about', ['visible' => false, 'content' => []])->assertRedirect();
    $this->get('/about')->assertNotFound();

    $this->actingAs($admin)->put('/admin/content/about', ['visible' => true, 'content' => []])->assertRedirect();
    $this->get('/about')->assertOk();
});

it('exposes the visibility flag in the editor', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/content/services')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('visible', true));
});

it('cleans empty repeater rows and list items on save', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/content/about', [
            'content' => [
                'principles' => [
                    'enabled' => true,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Kept', 'body' => 'Has content'],
                        ['icon' => '', 'title' => '', 'body' => ''],
                    ],
                ],
            ],
        ])
        ->assertRedirect();

    $items = Page::where('slug', 'about')->first()->blocks['principles']['items'];
    expect($items)->toHaveCount(1);
    expect($items[0]['title'])->toBe('Kept');
});

it('saves list subfields inside repeater items and serves them on the live page', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/content/process', [
            'content' => [
                'engagement' => [
                    'enabled' => true,
                    'items' => [
                        [
                            'icon' => 'compass',
                            'title' => 'Fixed scope',
                            'description' => 'A defined outcome, a fixed timeline.',
                            'points' => ['Scoped milestones with weekly demos', '  ', 'Defined acceptance criteria'],
                        ],
                    ],
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $items = Page::where('slug', 'process')->first()->blocks['engagement']['items'];
    expect($items)->toHaveCount(1);
    expect($items[0]['points'])->toBe(['Scoped milestones with weekly demos', 'Defined acceptance criteria']);

    // The merged content served to the public page carries the nested list.
    $this->get('/process')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('content.engagement.items.0.points', ['Scoped milestones with weekly demos', 'Defined acceptance criteria'])
        );
});

it('rejects a non-array value for a list subfield inside a repeater', function () {
    $this->actingAs(User::factory()->create())
        ->from('/admin/content/process')
        ->put('/admin/content/process', [
            'content' => [
                'engagement' => [
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Fixed scope', 'description' => 'Desc', 'points' => 'not-an-array'],
                    ],
                ],
            ],
        ])
        ->assertSessionHasErrors('content.engagement.items.0.points');
});
