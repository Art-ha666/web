<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CtaSection;
use App\Models\Page;
use App\Models\ProcessStep;
use App\Services\PageContentService;
use Inertia\Inertia;
use Inertia\Response;

class ProcessController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('process'), 404);

        return Inertia::render('public/Process', [
            'content' => $content->for('process'),
            'page' => Page::where('slug', 'process')->first(['title', 'blocks', 'seo_title', 'seo_description']),
            'steps' => ProcessStep::query()->active()->ordered()
                ->get(['number', 'title', 'description', 'deliverable_tag', 'icon']),
            'cta' => CtaSection::forKey('closing'),
        ]);
    }
}
