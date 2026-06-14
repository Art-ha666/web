<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CtaSection;
use App\Models\Project;
use App\Services\PageContentService;
use Inertia\Inertia;
use Inertia\Response;

class WorkController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('work'), 404);

        return Inertia::render('public/Work', [
            'content' => $content->for('work'),
            'projects' => Project::query()->published()->ordered()
                ->get(['id', 'title', 'slug', 'client_type', 'industry', 'year', 'category_tags', 'headline_result', 'results', 'tech_stack', 'featured']),
        ]);
    }

    public function show(PageContentService $content, Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $project->load('service:id,title,slug', 'testimonials:id,project_id,quote,author_name,author_role,company_name');

        return Inertia::render('public/WorkShow', [
            'content' => $content->for('work'),
            'closingCta' => CtaSection::forKey('closing'),
            'meta' => [
                'title' => $project->seo_title ?: $project->title,
                'description' => $project->seo_description ?: $project->summary,
            ],
            'project' => $project,
            'related' => Project::query()->published()->where('id', '!=', $project->id)->ordered()
                ->limit(3)->get(['title', 'slug', 'industry', 'year', 'headline_result']),
        ]);
    }
}
