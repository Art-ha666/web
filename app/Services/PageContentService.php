<?php

namespace App\Services;

use App\Models\Page;

/**
 * Drives the "every page, every section, every string is editable" system.
 *
 * Each public page has a schema file at config/page_content/{slug}.php that
 * declares its sections, fields and DEFAULT copy. Admin overrides are stored on
 * the matching Page's `blocks` JSON. The merged result is handed to the page's
 * Vue component, so the bespoke design renders unchanged until something is
 * edited, then reflects the admin's wording instantly.
 */
class PageContentService
{
    /**
     * The page slugs that expose a structured content editor, in admin order.
     *
     * @var array<int, string>
     */
    public const PAGES = ['about', 'services', 'work', 'process', 'team', 'insights', 'careers', 'contact'];

    /**
     * Raw schema (sections + fields + defaults) for a page.
     *
     * @return array<string, mixed>
     */
    public function schema(string $slug): array
    {
        $path = config_path("page_content/{$slug}.php");

        return is_file($path) ? require $path : [];
    }

    /**
     * Listing for the admin content index.
     *
     * @return array<int, array{slug: string, label: string, route: string}>
     */
    public function pages(): array
    {
        $out = [];
        foreach (self::PAGES as $slug) {
            $schema = $this->schema($slug);
            if ($schema === []) {
                continue;
            }
            $out[] = [
                'slug' => $slug,
                'label' => $schema['label'] ?? ucfirst($slug),
                'route' => $schema['route'] ?? '/'.$slug,
            ];
        }

        return $out;
    }

    /**
     * Merged content (defaults overlaid with admin overrides) for a page,
     * shaped as { section: { enabled?, field: value, ... } }.
     *
     * @return array<string, mixed>
     */
    public function for(string $slug): array
    {
        $schema = $this->schema($slug);
        $sections = $schema['sections'] ?? [];
        $overrides = $this->overrides($slug);

        $content = [];
        foreach ($sections as $key => $section) {
            $node = [];

            if (($section['toggle'] ?? false) === true) {
                $node['enabled'] = $overrides[$key]['enabled'] ?? true;
            }

            foreach ($section['fields'] ?? [] as $field => $meta) {
                $override = $overrides[$key][$field] ?? null;
                $node[$field] = $this->resolveValue($meta, $override);
            }

            $content[$key] = $node;
        }

        return $content;
    }

    /**
     * The defaults-only content (used to reset / seed the editor).
     *
     * @return array<string, mixed>
     */
    public function defaults(string $slug): array
    {
        $schema = $this->schema($slug);
        $content = [];

        foreach ($schema['sections'] ?? [] as $key => $section) {
            $node = [];
            if (($section['toggle'] ?? false) === true) {
                $node['enabled'] = true;
            }
            foreach ($section['fields'] ?? [] as $field => $meta) {
                $node[$field] = $meta['default'] ?? ($meta['type'] === 'list' || $meta['type'] === 'repeater' ? [] : '');
            }
            $content[$key] = $node;
        }

        return $content;
    }

    /**
     * Whether the page is live. A page set to "draft" in the editor is hidden
     * (its public route 404s). Pages with no row yet default to visible.
     */
    public function isVisible(string $slug): bool
    {
        return Page::query()->where('slug', $slug)->value('status') !== 'draft';
    }

    /**
     * Persist admin overrides for a page onto its Page.blocks.
     *
     * @param  array<string, mixed>  $values
     */
    public function save(string $slug, array $values, ?string $status = null): void
    {
        $page = Page::firstOrNew(['slug' => $slug]);
        $schema = $this->schema($slug);

        $page->fill([
            'title' => $page->title ?: ($schema['label'] ?? ucfirst($slug)),
            'status' => in_array($status, ['draft', 'published'], true) ? $status : ($page->status ?: 'published'),
            'is_system' => true,
            'blocks' => $values,
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    protected function overrides(string $slug): array
    {
        $blocks = Page::where('slug', $slug)->value('blocks');

        return is_array($blocks) ? $blocks : [];
    }

    /**
     * Coerce a stored/override value to the field's type, falling back to default.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function resolveValue(array $meta, mixed $override): mixed
    {
        $type = $meta['type'] ?? 'text';
        $default = $meta['default'] ?? ($type === 'list' || $type === 'repeater' ? [] : '');

        if ($override === null) {
            return $default;
        }

        return match ($type) {
            'list', 'repeater' => is_array($override) ? $override : $default,
            default => is_scalar($override) ? (string) $override : $default,
        };
    }
}
