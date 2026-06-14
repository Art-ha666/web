<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        {{-- AKH active design theme custom properties (server-injected, no FOUC) --}}
        <style id="ak-theme-vars">{!! app(\App\Services\ThemeService::class)->cssVariables() !!}</style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        {{-- SEO (server-rendered per page for crawlers & social scrapers, which do not run JS).
             Resolution order: explicit 'meta' prop > page_content seo section > Page seo columns > settings defaults. --}}
        @php
            try { $akSettings = \App\Models\SiteSetting::current(); } catch (\Throwable $e) { $akSettings = null; }
            $akProps = (array) ($page['props'] ?? []);
            $akComponent = (string) ($page['component'] ?? '');
            $akMeta = (array) ($akProps['meta'] ?? []);
            $akContentSeo = (array) ($akProps['content']['seo'] ?? []);
            $akPageSeo = is_array($akProps['page'] ?? null) ? $akProps['page'] : [];

            $akName = $akSettings->site_name ?? config('app.name', 'AKH Solutions');
            $akTitle = trim($akMeta['title'] ?? $akContentSeo['title'] ?? $akPageSeo['seo_title'] ?? $akSettings->default_meta_title ?? $akName);
            $akDesc = trim($akMeta['description'] ?? $akContentSeo['description'] ?? $akPageSeo['seo_description'] ?? $akSettings->default_meta_description
                ?? 'A software, AI, and IT engineering agency building across web, mobile, AI, embedded, robotics, IoT, networking, and cloud.');
            $akUrl = url()->current();
            $akImage = filled($akSettings->og_image ?? null) ? url($akSettings->og_image) : url('/og-default.png');
            $akType = $akMeta['type'] ?? 'website';
            $akNoindex = ($akMeta['noindex'] ?? false) || \Illuminate\Support\Str::startsWith($akComponent, ['auth/', 'admin/', 'settings/']);
        @endphp
        <meta name="description" content="{{ $akDesc }}">
        <meta name="robots" content="{{ $akNoindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' }}">
        <link rel="canonical" href="{{ $akUrl }}">
        <meta name="theme-color" content="#07080d">
        <meta property="og:type" content="{{ $akType }}">
        <meta property="og:locale" content="en_US">
        <meta property="og:site_name" content="{{ $akName }}">
        <meta property="og:title" content="{{ $akTitle }}">
        <meta property="og:description" content="{{ $akDesc }}">
        <meta property="og:url" content="{{ $akUrl }}">
        <meta property="og:image" content="{{ $akImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        @if ($akType === 'article')
            @if (filled($akMeta['published_time'] ?? null))
                <meta property="article:published_time" content="{{ $akMeta['published_time'] }}">
            @endif
            @if (filled($akMeta['modified_time'] ?? null))
                <meta property="article:modified_time" content="{{ $akMeta['modified_time'] }}">
            @endif
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $akTitle }}">
        <meta name="twitter:description" content="{{ $akDesc }}">
        <meta name="twitter:image" content="{{ $akImage }}">
        {{-- Admin-managed custom head scripts (Admin → Settings → Analytics & scripts) --}}
        @if (filled($akSettings->head_scripts ?? null))
            {!! $akSettings->head_scripts !!}
        @endif

        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $akName,
                'url' => url('/'),
                'description' => $akSettings->default_meta_description ?? $akDesc,
                'logo' => $akImage,
                'email' => $akSettings->primary_email ?? null,
                'sameAs' => array_values(array_filter((array) ($akSettings->socials ?? []))),
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
        @if ($akType === 'article' && is_array($akProps['article'] ?? null))
            <script type="application/ld+json">
                {!! json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => $akProps['article']['title'] ?? $akTitle,
                    'description' => $akDesc,
                    'datePublished' => $akMeta['published_time'] ?? null,
                    'dateModified' => $akMeta['modified_time'] ?? null,
                    'author' => filled($akMeta['author'] ?? null)
                        ? ['@type' => 'Person', 'name' => $akMeta['author']]
                        : ['@type' => 'Organization', 'name' => $akName],
                    'publisher' => ['@type' => 'Organization', 'name' => $akName, 'logo' => ['@type' => 'ImageObject', 'url' => $akImage]],
                    'image' => $akImage,
                    'mainEntityOfPage' => $akUrl,
                ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endif
        @if (! empty($akProps['breadcrumbs']) && is_array($akProps['breadcrumbs']))
            <script type="application/ld+json">
                {!! json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => collect($akProps['breadcrumbs'])
                        ->map(fn (array $crumb, int $i) => [
                            '@type' => 'ListItem',
                            'position' => $i + 1,
                            'name' => $crumb['title'] ?? '',
                            'item' => url($crumb['url'] ?? '/'),
                        ])
                        ->push([
                            '@type' => 'ListItem',
                            'position' => count($akProps['breadcrumbs']) + 1,
                            'name' => $akPageSeo['title'] ?? $akTitle,
                        ])->values()->all(),
                ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endif

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ $akTitle }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
