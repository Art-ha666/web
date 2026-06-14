<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Render a CMS page. Nested pages resolve by their full slug path
     * (/page/parent/child); a stale path for an existing page redirects
     * to its canonical URL so old links keep working.
     */
    public function show(string $slug): Response|RedirectResponse
    {
        $page = Page::query()->published()->where('slug', Str::afterLast($slug, '/'))->firstOrFail();

        $canonical = $page->publicPath();

        if ($canonical !== "/page/{$slug}" && request()->path() === "page/{$slug}") {
            return redirect($canonical, 301);
        }

        return Inertia::render('public/Page', [
            'page' => $page->only(['title', 'slug', 'blocks', 'seo_title', 'seo_description']),
            'breadcrumbs' => $page->ancestors()
                ->filter(fn (Page $ancestor): bool => $ancestor->status === 'published')
                ->map(fn (Page $ancestor): array => [
                    'title' => $ancestor->title,
                    'url' => $ancestor->publicPath(),
                ])->values()->all(),
        ]);
    }
}
