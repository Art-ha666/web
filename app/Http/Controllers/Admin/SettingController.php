<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('admin/Settings', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request, ThemeService $themes): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'primary_email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'whatsapp' => ['nullable', 'string', 'max:60'],
            'telegram' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:200'],
            'socials' => ['nullable', 'array'],
            'socials.*' => ['nullable', 'string', 'max:200'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['nullable', 'string', 'max:80'],
            'nav_cta_label' => ['required', 'string', 'max:60'],
            'nav_cta_url' => ['required', 'string', 'max:160'],
            'footer_blurb' => ['nullable', 'string', 'max:500'],
            'default_meta_title' => ['nullable', 'string', 'max:160'],
            'default_meta_description' => ['nullable', 'string', 'max:300'],
            'announcement_text' => ['nullable', 'string', 'max:200'],
            'announcement_active' => ['boolean'],
            'og_image' => ['nullable', 'string', 'max:300'],
            'ga_measurement_id' => ['nullable', 'string', 'max:40', 'regex:/^(G|UA|AW|GTM)-[A-Za-z0-9\-]+$/'],
            'head_scripts' => ['nullable', 'string', 'max:5000'],
            'newsletter_heading' => ['nullable', 'string', 'max:120'],
            'newsletter_placeholder' => ['nullable', 'string', 'max:120'],
            'newsletter_success' => ['nullable', 'string', 'max:200'],
            'cookie_banner_text' => ['nullable', 'string', 'max:500'],
            'cookie_accept_label' => ['nullable', 'string', 'max:60'],
            'cookie_decline_label' => ['nullable', 'string', 'max:60'],
        ], [
            'ga_measurement_id.regex' => 'Use a Google tag ID like G-XXXXXXXXXX.',
        ]);

        $settings = SiteSetting::current();
        $settings->update([
            ...$validated,
            'socials' => array_filter($validated['socials'] ?? []),
            'locations' => array_values(array_filter($validated['locations'] ?? [])),
            'announcement_active' => $request->boolean('announcement_active'),
        ]);

        $themes->flush();

        return back()->with('success', 'Settings saved.');
    }
}
