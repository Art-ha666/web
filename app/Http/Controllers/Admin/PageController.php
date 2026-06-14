<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/pages/Index', [
            'pages' => Page::query()->orderByDesc('is_system')->orderBy('title')
                ->get(['id', 'title', 'slug', 'parent_id', 'status', 'is_system', 'show_in_nav', 'updated_at'])
                ->map(fn (Page $page): array => [
                    ...$page->only(['id', 'title', 'slug', 'parent_id', 'status', 'is_system', 'show_in_nav', 'updated_at']),
                    'public_path' => $page->publicPath(),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/pages/Edit', [
            'page' => null,
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);

        Page::create($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page): Response
    {
        return Inertia::render('admin/pages/Edit', [
            'page' => [...$page->toArray(), 'public_path' => $page->publicPath()],
            'parentOptions' => $this->parentOptions($page),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validateData($request, $page);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);

        $page->update($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page saved.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        abort_if($page->is_system, 403, 'System pages cannot be deleted.');

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    /**
     * Pages that can act as a parent - system content pages and (when editing)
     * the page itself plus its descendants are excluded to prevent cycles.
     *
     * @return array<int, array{id: int, title: string, path: string}>
     */
    protected function parentOptions(?Page $current = null): array
    {
        return Page::query()->where('is_system', false)->orderBy('title')->get()
            ->reject(fn (Page $candidate): bool => $current !== null && $candidate->isSelfOrDescendantOf($current))
            ->map(fn (Page $candidate): array => [
                'id' => $candidate->id,
                'title' => $candidate->title,
                'path' => $candidate->publicPath(),
            ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable', 'string', 'max:160', 'alpha_dash',
                Rule::unique('pages', 'slug')->ignore($page?->id),
                Rule::when(! $page?->is_system, [Rule::notIn(Page::RESERVED_SLUGS)]),
            ],
            'parent_id' => ['nullable', 'integer', Rule::exists('pages', 'id')],
            'blocks' => ['nullable', 'array'],
            'blocks.*.type' => ['nullable', 'string', 'max:40'],
            'blocks.*.data' => ['nullable', 'array'],
            'blocks.*.data.html' => ['nullable', 'string', 'max:60000'],
            'blocks.*.data.text' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'show_in_nav' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ], [
            'slug.not_in' => 'This slug is reserved for a built-in page.',
        ]);

        // System pages keep their fixed routes: never nest or re-slug them.
        if ($page?->is_system) {
            $data['parent_id'] = null;
            $data['slug'] = $page->slug;
        }

        // Re-parenting to itself or one of its own descendants would create a cycle.
        if ($page !== null && ($data['parent_id'] ?? null) !== null) {
            $parent = Page::find($data['parent_id']);

            if ($parent !== null && $parent->isSelfOrDescendantOf($page)) {
                abort(422, 'A page cannot be nested under itself or one of its descendants.');
            }
        }

        return $data;
    }
}
