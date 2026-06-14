<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CtaSection;
use App\Models\Stat;
use App\Models\TeamMember;
use App\Services\PageContentService;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('about'), 404);

        return Inertia::render('public/About', [
            'content' => $content->for('about'),
            'team' => TeamMember::query()->active()->ordered()
                ->get(['id', 'name', 'slug', 'role', 'specialty', 'bio', 'years_experience', 'linkedin', 'github']),
            'stats' => Stat::query()->active()->group('band')->ordered()->get(['value', 'prefix', 'suffix', 'label']),
            'manifesto' => CtaSection::forKey('manifesto'),
            'cta' => CtaSection::forKey('closing'),
        ]);
    }
}
