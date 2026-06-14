<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeContentController extends Controller
{
    /**
     * Every homepage section the admin can toggle on/off and reword.
     *
     * @var array<string, array{label: string, fields: array<int, string>}>
     */
    public const SECTIONS = [
        'services' => ['label' => 'Services', 'fields' => ['eyebrow', 'title', 'subtitle', 'view_all_label', 'explore_label']],
        'stats' => ['label' => 'Stats band', 'fields' => ['eyebrow', 'title']],
        'process' => ['label' => 'Process', 'fields' => ['eyebrow', 'title']],
        'work' => ['label' => 'Selected work', 'fields' => ['eyebrow', 'title', 'view_all_label']],
        'manifesto' => ['label' => 'Manifesto band', 'fields' => []],
        'team' => ['label' => 'Team', 'fields' => ['eyebrow', 'title', 'view_all_label']],
        'testimonials' => ['label' => 'Testimonials', 'fields' => ['eyebrow', 'title']],
        'closing' => ['label' => 'Closing call-to-action', 'fields' => []],
    ];

    public function edit(): Response
    {
        $blocks = $this->homeBlocks();

        return Inertia::render('admin/Home', [
            'hero' => $blocks['hero'] ?? new \stdClass,
            'partners' => $blocks['partners'] ?? ['enabled' => true, 'eyebrow' => ''],
            'sections' => $blocks['sections'] ?? new \stdClass,
            'sectionMeta' => self::SECTIONS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero' => ['array'],
            'hero.badge' => ['nullable', 'string', 'max:160'],
            'hero.eyebrow' => ['nullable', 'string', 'max:120'],
            'hero.headline_line1' => ['nullable', 'string', 'max:160'],
            'hero.headline_line2' => ['nullable', 'string', 'max:160'],
            'hero.accent_word' => ['nullable', 'string', 'max:60'],
            'hero.subhead' => ['nullable', 'string', 'max:400'],
            'hero.primary_label' => ['nullable', 'string', 'max:60'],
            'hero.primary_url' => ['nullable', 'string', 'max:160'],
            'hero.secondary_label' => ['nullable', 'string', 'max:60'],
            'hero.secondary_url' => ['nullable', 'string', 'max:160'],
            'hero.trust_items' => ['array'],
            'hero.trust_items.*' => ['nullable', 'string', 'max:60'],

            'partners' => ['array'],
            'partners.enabled' => ['boolean'],
            'partners.eyebrow' => ['nullable', 'string', 'max:120'],

            'sections' => ['array'],
            'sections.*.enabled' => ['boolean'],
            'sections.*.eyebrow' => ['nullable', 'string', 'max:120'],
            'sections.*.title' => ['nullable', 'string', 'max:160'],
            'sections.*.subtitle' => ['nullable', 'string', 'max:240'],
            'sections.*.view_all_label' => ['nullable', 'string', 'max:60'],
            'sections.*.explore_label' => ['nullable', 'string', 'max:60'],
        ]);

        $page = Page::firstOrNew(['slug' => 'home']);
        $blocks = is_array($page->blocks) ? $page->blocks : [];

        // Hero - merge so unrelated keys survive; trim/clean the trust micro-row.
        $hero = $validated['hero'] ?? [];
        if (array_key_exists('trust_items', $hero)) {
            $hero['trust_items'] = array_values(array_filter(
                array_map(static fn ($v): string => trim((string) $v), $hero['trust_items']),
                static fn (string $v): bool => $v !== '',
            ));
        }
        $blocks['hero'] = array_merge($blocks['hero'] ?? [], $hero);

        // Partners marquee section.
        $partners = $validated['partners'] ?? [];
        $blocks['partners'] = [
            'enabled' => (bool) ($partners['enabled'] ?? true),
            'eyebrow' => $partners['eyebrow'] ?? ($blocks['partners']['eyebrow'] ?? ''),
        ];

        // Per-section toggle + wording.
        $sections = $blocks['sections'] ?? [];
        foreach (($validated['sections'] ?? []) as $key => $vals) {
            if (! array_key_exists($key, self::SECTIONS)) {
                continue;
            }

            $sections[$key] = array_merge(
                $sections[$key] ?? [],
                ['enabled' => (bool) ($vals['enabled'] ?? true)],
                array_intersect_key($vals, array_flip(['eyebrow', 'title', 'subtitle', 'view_all_label', 'explore_label'])),
            );

            // Blank label overrides fall back to the built-in defaults on the public page.
            foreach (['view_all_label', 'explore_label'] as $labelField) {
                if (isset($sections[$key][$labelField]) && trim((string) $sections[$key][$labelField]) === '') {
                    unset($sections[$key][$labelField]);
                }
            }
        }
        $blocks['sections'] = $sections;

        $page->fill([
            'title' => $page->title ?: 'Home',
            'status' => $page->status ?: 'published',
            'is_system' => true,
            'blocks' => $blocks,
        ])->save();

        return back()->with('success', 'Homepage updated.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function homeBlocks(): array
    {
        $page = Page::where('slug', 'home')->first();

        return is_array($page?->blocks) ? $page->blocks : [];
    }
}
