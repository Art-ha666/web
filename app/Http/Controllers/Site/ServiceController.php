<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\PageContentService;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('services'), 404);

        return Inertia::render('public/Services', [
            'content' => $content->for('services'),
            'services' => Service::query()->active()->ordered()
                ->get(['id', 'title', 'slug', 'icon', 'tab_label', 'short_blurb', 'value_metric', 'benefit_bullets', 'gradient', 'tech_stack']),
        ]);
    }

    public function show(PageContentService $content, Service $service): Response
    {
        abort_unless($service->is_active, 404);

        $service->load(['projects' => fn ($q) => $q->published()->ordered()
            ->select(['id', 'title', 'slug', 'related_service_id', 'industry', 'year', 'headline_result'])]);

        return Inertia::render('public/ServiceShow', [
            'content' => $content->for('services'),
            'meta' => [
                'title' => $service->seo_title ?: $service->title,
                'description' => $service->seo_description ?: $service->short_blurb,
            ],
            'service' => $service,
            'related' => Service::query()->active()->where('id', '!=', $service->id)->ordered()
                ->limit(4)->get(['title', 'slug', 'icon', 'short_blurb']),
        ]);
    }
}
