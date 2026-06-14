<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Mail\LeadReceived;
use App\Models\Lead;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Services\PageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(PageContentService $content): Response
    {
        abort_if(! $content->isVisible('contact'), 404);

        $page = $content->for('contact');

        return Inertia::render('public/Contact', [
            'content' => $page,
            'services' => Service::query()->active()->ordered()->pluck('title'),
            'budgetRanges' => $page['form']['budget_ranges'] ?? [],
        ]);
    }

    public function store(StoreLeadRequest $request, PageContentService $content): RedirectResponse
    {
        $lead = Lead::create([
            ...$request->safe()->except(['website', 'consent_marketing', 'consent_data_processing']),
            'consent_marketing' => $request->boolean('consent_marketing'),
            'consent_data_processing' => $request->boolean('consent_data_processing'),
            'source_page' => $request->input('source_page', '/contact'),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $recipient = SiteSetting::current()->primary_email ?: config('mail.from.address');

        try {
            Mail::to($recipient)->send(new LeadReceived($lead));
        } catch (\Throwable $e) {
            report($e);
        }

        $flash = (string) ($content->for('contact')['form']['success_flash'] ?? '');
        if ($flash === '') {
            $flash = "Thanks, {name} - we'll reply within one business day.";
        }

        return back()->with('success', str_replace('{name}', $lead->name, $flash));
    }
}
