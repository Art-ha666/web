<?php

use App\Http\Middleware\CacheGuestPages;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ShareSiteData;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Behind the Cloudflare Tunnel (cloudflared -> localhost), honour the
        // X-Forwarded-* headers so Laravel knows requests arrive over HTTPS.
        // Trust only loopback + private ranges (cloudflared/docker), never '*'.
        $middleware->trustProxies(at: [
            '127.0.0.0/8',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Edge-cacheability for anonymous marketing pages. Prepended so its
        // response phase runs LAST (outermost) - after StartSession + CSRF have
        // attached their Set-Cookie headers - so it can strip them and stamp a
        // public s-maxage. Cloudflare then serves those pages from the edge in
        // milliseconds and the origin stops rendering them under load.
        $middleware->web(prepend: [
            CacheGuestPages::class,
        ]);

        // The two public lead forms post from pages that are now served
        // cookieless (no XSRF-TOKEN cookie for first-time visitors). They are
        // already hardened with a honeypot + per-IP throttle + strict
        // validation, so exempt them from CSRF rather than break submissions.
        $middleware->validateCsrfTokens(except: [
            'contact',
            'newsletter',
        ]);

        $middleware->alias([
            'site.data' => ShareSiteData::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
