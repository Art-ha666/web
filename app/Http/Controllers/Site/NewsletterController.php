<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:0'], // honeypot
        ]);

        if (! empty($validated['company'] ?? null)) {
            return back();
        }

        Subscriber::firstOrCreate(
            ['email' => $validated['email']],
            ['source' => 'footer'],
        );

        return back()->with('success', SiteSetting::current()->newsletter_success ?: "You're on the list - engineering notes incoming.");
    }
}
