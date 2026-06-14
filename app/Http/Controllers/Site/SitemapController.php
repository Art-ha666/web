<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\JobPosting;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Services\PageContentService;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(protected PageContentService $content) {}

    /**
     * A fully automatic sitemap: rebuilt from live data on every request, so any
     * page, service, case study, post, job, or admin-created CMS page is included
     * the moment it is published - no manual step, no regeneration command.
     */
    public function index(): Response
    {
        /** @var array<int, array{loc: string, lastmod: ?string}> $entries */
        $entries = [];
        $seen = [];

        $add = function (string $path, ?CarbonInterface $lastmod = null) use (&$entries, &$seen): void {
            if (isset($seen[$path])) {
                return;
            }
            $seen[$path] = true;
            $entries[] = ['loc' => url($path), 'lastmod' => $lastmod?->toDateString()];
        };

        // Core marketing routes - skip any content page the admin has hidden
        // (draft). One query for all eight slugs instead of one each.
        $coreSlugs = ['services', 'work', 'process', 'about', 'team', 'insights', 'careers', 'contact'];
        $statuses = Page::query()->whereIn('slug', $coreSlugs)->pluck('status', 'slug');

        $add('/');
        foreach ($coreSlugs as $slug) {
            if (($statuses[$slug] ?? null) !== 'draft') {
                $add("/{$slug}");
            }
        }

        Service::query()->where('is_active', true)->get(['slug', 'updated_at'])
            ->each(fn (Service $s) => $add("/services/{$s->slug}", $s->updated_at));

        Project::query()->where('is_published', true)->get(['slug', 'updated_at'])
            ->each(fn (Project $p) => $add("/work/{$p->slug}", $p->updated_at));

        Article::query()->where('status', 'published')->get(['slug', 'updated_at'])
            ->each(fn (Article $a) => $add("/insights/{$a->slug}", $a->updated_at));

        JobPosting::query()->where('is_open', true)->get(['slug', 'updated_at'])
            ->each(fn (JobPosting $j) => $add("/careers/{$j->slug}", $j->updated_at));

        // Every published CMS page (legal + any admin-created page), mapped to its
        // canonical public URL - content pages that already own a top-level route
        // (e.g. /careers) are skipped so the sitemap never lists duplicates.
        // Parent relations are pre-wired from one query so publicPath() never
        // walks ancestors via additional queries.
        $pages = Page::query()->where('status', 'published')->get();
        $pagesById = $pages->keyBy('id');
        $pages->each(function (Page $page) use ($pagesById): void {
            $page->setRelation('parent', $page->parent_id !== null ? $pagesById->get($page->parent_id) : null);
        });

        $pages->each(function (Page $page) use ($add): void {
            if (Page::ownsTopLevelRoute($page->slug) && ! in_array($page->slug, ['privacy', 'cookies', 'terms'], true) && $page->slug !== 'home') {
                return;
            }

            $add($page->publicPath(), $page->updated_at);
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $xml .= '  <url><loc>'.e($entry['loc']).'</loc>';
            if ($entry['lastmod'] !== null) {
                $xml .= '<lastmod>'.$entry['lastmod'].'</lastmod>';
            }
            $xml .= "</url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
