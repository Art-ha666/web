<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Services\PageContentService;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('team'), 404);

        return Inertia::render('public/Team', [
            'content' => $content->for('team'),
            'team' => TeamMember::query()->active()->ordered()
                ->get(['id', 'name', 'slug', 'role', 'specialty', 'bio', 'years_experience', 'linkedin', 'github']),
        ]);
    }
}
