<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\Subscriber;
use App\Models\TeamMember;
use App\Models\Theme;
use App\Services\ThemeService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(ThemeService $themes): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'leads' => Lead::count(),
                'newLeads' => Lead::where('status', 'new')->count(),
                'services' => Service::count(),
                'projects' => Project::count(),
                'team' => TeamMember::count(),
                'articles' => Article::count(),
                'subscribers' => Subscriber::count(),
                'themes' => Theme::count(),
            ],
            'activeTheme' => $themes->meta(),
            'recentLeads' => Lead::query()->latest()->limit(6)
                ->get(['id', 'name', 'business_email', 'company', 'service_interest', 'status', 'created_at']),
        ]);
    }
}
