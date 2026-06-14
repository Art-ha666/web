<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Services\PageContentService;
use Inertia\Inertia;
use Inertia\Response;

class CareerController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('careers'), 404);

        return Inertia::render('public/Careers', [
            'content' => $content->for('careers'),
            'jobs' => JobPosting::query()->open()->orderBy('sort_order')
                ->get(['id', 'title', 'slug', 'department', 'location', 'employment_type', 'seniority', 'summary', 'tech_stack']),
        ]);
    }

    public function show(PageContentService $content, JobPosting $job): Response
    {
        abort_unless($job->is_open, 404);

        return Inertia::render('public/JobShow', [
            'content' => $content->for('careers'),
            'meta' => [
                'title' => $job->title.' - Careers',
                'description' => $job->summary,
            ],
            'job' => $job,
        ]);
    }
}
