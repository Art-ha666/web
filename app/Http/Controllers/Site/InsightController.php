<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CtaSection;
use App\Services\PageContentService;
use Inertia\Inertia;
use Inertia\Response;

class InsightController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('insights'), 404);

        return Inertia::render('public/Insights', [
            'content' => $content->for('insights'),
            'articles' => Article::query()->published()->with('author:id,name,role')
                ->orderByDesc('published_at')
                ->get(['id', 'title', 'slug', 'excerpt', 'tags', 'reading_time', 'published_at', 'author_id', 'featured']),
        ]);
    }

    public function show(PageContentService $content, Article $article): Response
    {
        abort_unless($article->status === 'published', 404);

        $article->load('author:id,name,slug,role,specialty');

        return Inertia::render('public/Article', [
            'content' => $content->for('insights'),
            'meta' => [
                'title' => $article->seo_title ?: $article->title,
                'description' => $article->seo_description ?: $article->excerpt,
                'type' => 'article',
                'published_time' => $article->published_at?->toAtomString(),
                'modified_time' => $article->updated_at?->toAtomString(),
                'author' => $article->author?->name,
            ],
            'article' => $article,
            'related' => Article::query()->published()->where('id', '!=', $article->id)
                ->orderByDesc('published_at')->limit(3)
                ->get(['title', 'slug', 'excerpt', 'reading_time', 'published_at']),
            'closingCta' => CtaSection::forKey('closing'),
        ]);
    }
}
