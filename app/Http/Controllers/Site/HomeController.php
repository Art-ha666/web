<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use App\Models\CtaSection;
use App\Models\Page;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\Service;
use App\Models\Stat;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $home = Page::where('slug', 'home')->first();

        return Inertia::render('public/Home', [
            'home' => $home?->blocks ?? [],
            'services' => Service::query()->active()->ordered()
                ->get(['id', 'title', 'slug', 'icon', 'tab_label', 'short_blurb', 'value_metric', 'benefit_bullets', 'gradient', 'tech_stack']),
            'stats' => [
                'band' => Stat::query()->active()->group('band')->ordered()->get(['value', 'prefix', 'suffix', 'label']),
                'hero' => Stat::query()->active()->group('hero')->ordered()->get(['value', 'prefix', 'suffix', 'label']),
            ],
            'process' => ProcessStep::query()->active()->ordered()
                ->get(['number', 'title', 'description', 'deliverable_tag', 'icon']),
            'projects' => Project::query()->published()->ordered()
                ->get(['id', 'title', 'slug', 'client_type', 'industry', 'year', 'category_tags', 'headline_result', 'results', 'featured']),
            'team' => TeamMember::query()->active()->ordered()
                ->get(['id', 'name', 'slug', 'role', 'specialty', 'years_experience', 'linkedin', 'github']),
            'testimonials' => Testimonial::query()->active()->ordered()
                ->get(['id', 'quote', 'author_name', 'author_role', 'company_name', 'rating']),
            'logos' => ClientLogo::query()->active()->ordered()->get(['name', 'type', 'logo_svg', 'url']),
            'cta' => CtaSection::forKey('closing'),
            'manifesto' => CtaSection::forKey('manifesto'),
        ]);
    }
}
