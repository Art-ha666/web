<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageContentController extends Controller
{
    public function __construct(protected PageContentService $content) {}

    public function index(): Response
    {
        return Inertia::render('admin/content/Index', [
            'pages' => $this->content->pages(),
        ]);
    }

    public function edit(string $slug): Response
    {
        $schema = $this->content->schema($slug);

        if ($schema === []) {
            throw new NotFoundHttpException;
        }

        return Inertia::render('admin/content/Edit', [
            'slug' => $slug,
            'label' => $schema['label'] ?? ucfirst($slug),
            'route' => $schema['route'] ?? '/'.$slug,
            'sections' => $this->editorSections($schema),
            'content' => $this->content->for($slug),
            'visible' => $this->content->isVisible($slug),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $schema = $this->content->schema($slug);

        if ($schema === []) {
            throw new NotFoundHttpException;
        }

        $validated = $request->validate($this->rules($schema) + ['visible' => ['boolean']]);
        $clean = $this->clean($schema, $validated['content'] ?? []);
        $status = $request->boolean('visible', true) ? 'published' : 'draft';

        $this->content->save($slug, $clean, $status);

        return back()->with('success', ($schema['label'] ?? 'Page').' content saved.');
    }

    public function reset(string $slug): RedirectResponse
    {
        $schema = $this->content->schema($slug);

        if ($schema === []) {
            throw new NotFoundHttpException;
        }

        $this->content->save($slug, $this->content->defaults($slug));

        return back()->with('success', 'Reset to the original wording.');
    }

    /**
     * Flatten the schema into a renderable shape for the generic Vue editor.
     *
     * @param  array<string, mixed>  $schema
     * @return array<int, array<string, mixed>>
     */
    protected function editorSections(array $schema): array
    {
        $out = [];
        foreach ($schema['sections'] ?? [] as $key => $section) {
            $fields = [];
            foreach ($section['fields'] ?? [] as $field => $meta) {
                $fields[] = [
                    'key' => $field,
                    'type' => $meta['type'] ?? 'text',
                    'label' => $meta['label'] ?? ucfirst(str_replace('_', ' ', $field)),
                    'subfields' => array_map(
                        fn (array $sf, string $sfKey): array => [
                            'key' => $sfKey,
                            'type' => $sf['type'] ?? 'text',
                            'label' => $sf['label'] ?? ucfirst($sfKey),
                        ],
                        array_values($meta['subfields'] ?? []),
                        array_keys($meta['subfields'] ?? []),
                    ),
                ];
            }
            $out[] = [
                'key' => $key,
                'label' => $section['label'] ?? ucfirst($key),
                'toggle' => (bool) ($section['toggle'] ?? false),
                'fields' => $fields,
            ];
        }

        return $out;
    }

    /**
     * Build validation rules from the schema.
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    protected function rules(array $schema): array
    {
        $rules = ['content' => ['array']];

        foreach ($schema['sections'] ?? [] as $key => $section) {
            if (($section['toggle'] ?? false) === true) {
                $rules["content.$key.enabled"] = ['boolean'];
            }

            foreach ($section['fields'] ?? [] as $field => $meta) {
                $path = "content.$key.$field";
                switch ($meta['type'] ?? 'text') {
                    case 'textarea':
                        $rules[$path] = ['nullable', 'string', 'max:4000'];
                        break;
                    case 'list':
                        $rules[$path] = ['array'];
                        $rules["$path.*"] = ['nullable', 'string', 'max:400'];
                        break;
                    case 'repeater':
                        $rules[$path] = ['array'];
                        foreach ($meta['subfields'] ?? [] as $sf => $sfMeta) {
                            if (($sfMeta['type'] ?? 'text') === 'list') {
                                $rules["$path.*.$sf"] = ['nullable', 'array'];
                                $rules["$path.*.$sf.*"] = ['nullable', 'string', 'max:400'];
                            } else {
                                $rules["$path.*.$sf"] = ['nullable', 'string', 'max:4000'];
                            }
                        }
                        break;
                    default:
                        $rules[$path] = ['nullable', 'string', 'max:400'];
                }
            }
        }

        return $rules;
    }

    /**
     * Normalise the submitted content: drop unknown keys, clean lists/repeaters.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function clean(array $schema, array $input): array
    {
        $out = [];

        foreach ($schema['sections'] ?? [] as $key => $section) {
            $node = [];
            $given = $input[$key] ?? [];

            if (($section['toggle'] ?? false) === true) {
                $node['enabled'] = (bool) ($given['enabled'] ?? true);
            }

            foreach ($section['fields'] ?? [] as $field => $meta) {
                $value = $given[$field] ?? null;
                $type = $meta['type'] ?? 'text';

                if ($type === 'list') {
                    $node[$field] = array_values(array_filter(
                        array_map(fn ($v): string => trim((string) $v), is_array($value) ? $value : []),
                        fn (string $v): bool => $v !== '',
                    ));
                } elseif ($type === 'repeater') {
                    $rows = [];
                    foreach (is_array($value) ? $value : [] as $row) {
                        $clean = [];
                        $hasContent = false;
                        foreach ($meta['subfields'] ?? [] as $sf => $sfMeta) {
                            if (($sfMeta['type'] ?? 'text') === 'list') {
                                $items = array_values(array_filter(
                                    array_map(fn ($v): string => trim((string) $v), is_array($row[$sf] ?? null) ? $row[$sf] : []),
                                    fn (string $v): bool => $v !== '',
                                ));
                                $clean[$sf] = $items;
                                $hasContent = $hasContent || $items !== [];
                            } else {
                                $cell = trim((string) ($row[$sf] ?? ''));
                                $clean[$sf] = $cell;
                                $hasContent = $hasContent || $cell !== '';
                            }
                        }
                        if ($hasContent) {
                            $rows[] = $clean;
                        }
                    }
                    $node[$field] = $rows;
                } else {
                    $node[$field] = is_scalar($value) ? (string) $value : '';
                }
            }

            $out[$key] = $node;
        }

        return $out;
    }
}
