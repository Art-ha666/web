<?php

namespace App\Http\Middleware;

use App\Models\NavItem;
use App\Models\Page;
use App\Models\Service;
use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareSiteData
{
    public function handle(Request $request, Closure $next): Response
    {
        Inertia::share([
            'site' => fn () => $this->site(),
            'nav' => fn () => $this->nav(),
        ]);

        return $next($request);
    }

    /**
     * @return array<string, mixed>
     */
    protected function site(): array
    {
        $settings = SiteSetting::current();

        return [
            'name' => $settings->site_name,
            'tagline' => $settings->tagline,
            'email' => $settings->primary_email,
            'phone' => $settings->phone,
            'whatsapp' => $settings->whatsapp,
            'telegram' => $settings->telegram,
            'address' => $settings->address,
            'socials' => $settings->socials ?? [],
            'locations' => $settings->locations ?? [],
            'navCta' => [
                'label' => $settings->nav_cta_label,
                'url' => $settings->nav_cta_url,
            ],
            'footerBlurb' => $settings->footer_blurb,
            'metaTitle' => $settings->default_meta_title,
            'metaDescription' => $settings->default_meta_description,
            'announcement' => $settings->announcement_active ? $settings->announcement_text : null,
            'newsletter' => [
                'heading' => $settings->newsletter_heading ?: 'Engineering notes',
                'placeholder' => $settings->newsletter_placeholder ?: 'you@company.com',
                'success' => $settings->newsletter_success ?: "You're on the list - engineering notes incoming.",
            ],
            'cookieConsent' => [
                'text' => $settings->cookie_banner_text ?: 'We use a few cookies to keep the site working and (only if you agree) to understand how it is used. See our Cookie Policy for details.',
                'acceptLabel' => $settings->cookie_accept_label ?: 'Accept',
                'declineLabel' => $settings->cookie_decline_label ?: 'Decline',
                'gaId' => $settings->ga_measurement_id,
            ],
            'legalPages' => Page::query()->published()
                ->whereIn('slug', ['privacy', 'cookies', 'terms'])
                ->get(['slug', 'title'])
                ->map(fn (Page $page): array => ['slug' => $page->slug, 'title' => $page->title, 'url' => '/'.$page->slug])
                ->values()
                ->all(),
            'year' => (int) now()->format('Y'),
        ];
    }

    /**
     * @return array{header: array<int, array<string, mixed>>, footer: array<string, array<int, array<string, string>>>}
     */
    protected function nav(): array
    {
        $items = NavItem::query()->active()->orderBy('sort_order')->get();

        $header = $items->where('location', 'header')->values()
            ->map(fn (NavItem $item): array => [
                'label' => $item->label,
                'url' => $item->url,
            ])->all();

        $footer = $items->where('location', 'footer')
            ->groupBy('mega_group')
            ->map(fn ($group) => $group->map(fn (NavItem $item): array => [
                'label' => $item->label,
                'url' => $item->url,
            ])->values()->all())
            ->all();

        $services = Service::query()->active()->ordered()
            ->get(['title', 'slug', 'icon', 'short_blurb'])
            ->map(fn (Service $s): array => [
                'label' => $s->title,
                'url' => '/services/'.$s->slug,
                'icon' => $s->icon,
                'description' => $s->short_blurb,
            ])->all();

        return [
            'header' => $header,
            'footer' => $footer,
            'services' => $services,
        ];
    }
}
