<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Makes anonymous public marketing GET pages edge-cacheable at Cloudflare.
 *
 * Prepended to the `web` group, so its response phase runs LAST in the onion:
 * AFTER StartSession + AddQueuedCookiesToResponse have attached the session and
 * XSRF-TOKEN cookies. For a guest + GET + 200 + HTML response on a non-excluded
 * path it strips every Set-Cookie header and replaces the framework default
 * `no-cache, private` with `public, s-maxage=...`, so Cloudflare stores one
 * shared copy and never hits PHP again until it expires. This offloads
 * essentially all anonymous traffic to the edge - the box stops rendering (and
 * stops the per-request session DB read/write) for the common case.
 *
 * Everything else - POST/PUT/PATCH/DELETE, /admin, auth, /settings, logged-in
 * users, Inertia XHR partials, redirects, error responses, and any response
 * carrying flash data - is left fully dynamic and keeps its cookies.
 */
class CacheGuestPages
{
    /**
     * Edge cache TTL in seconds (Cloudflare s-maxage). The browser is told
     * max-age=0 so it always revalidates; only the shared CDN cache holds it.
     */
    private const EDGE_TTL = 300;

    /**
     * Path patterns that must NEVER be cached (kept fully dynamic).
     *
     * @var array<int, string>
     */
    private const NEVER_CACHE = [
        'admin',
        'admin/*',
        'login',
        'logout',
        'register',
        'forgot-password',
        'reset-password',
        'two-factor-challenge',
        'user/*',
        'settings',
        'settings/*',
        'dashboard',
        'up',
        'fpm-status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldCache($request, $response)) {
            return $response;
        }

        // Drop the session + XSRF Set-Cookie headers queued by inner middleware
        // so the cached copy is identical for every anonymous visitor and never
        // pins a shared session/CSRF token onto them.
        foreach ($response->headers->getCookies() as $cookie) {
            $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
        }
        $response->headers->remove('Set-Cookie');

        // Shared edge cache only; the private browser cache stays off so a user
        // who later logs in is never served a stale guest shell from disk.
        $response->headers->set(
            'Cache-Control',
            'public, max-age=0, s-maxage='.self::EDGE_TTL.', stale-while-revalidate=60'
        );
        $response->headers->remove('Pragma');
        $response->headers->remove('Expires');

        // We stripped Set-Cookie, so the response no longer varies by it.
        $response->headers->remove('Vary');

        return $response;
    }

    private function shouldCache(Request $request, Response $response): bool
    {
        return $this->cacheSkipReason($request, $response) === null;
    }

    private function cacheSkipReason(Request $request, Response $response): ?string
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return 'method';
        }

        if ($response->getStatusCode() !== 200) {
            return 'status:'.$response->getStatusCode();
        }

        // Authenticated requests must stay dynamic and keep their session.
        if ($request->user() !== null) {
            return 'auth';
        }

        // Inertia XHR partial visits return per-visit JSON; only the full
        // initial HTML document is safe to cache.
        if ($request->headers->has('X-Inertia')) {
            return 'inertia';
        }

        // Never cache a response that carries one-request flash data (e.g. the
        // page a contact submit redirected back to). Check for actual flashed
        // keys - every fresh session initialises `_flash.new` to an empty array,
        // so has() would be true even with no real flash content.
        if ($request->hasSession() && ! empty($request->session()->get('_flash.new'))) {
            return 'flash';
        }

        // Never strip cookies off file downloads / streamed responses.
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return 'stream';
        }

        // Only cache HTML documents. Symfony sets the default text/html
        // Content-Type later in prepare(), so at middleware time a plain HTML
        // view has NO Content-Type header yet - treat that (empty) as HTML.
        // Explicitly non-HTML types (sitemap.xml -> text/xml, robots.txt ->
        // text/plain, JSON) set their header eagerly and are excluded here.
        $contentType = (string) $response->headers->get('Content-Type', '');
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return 'ctype:'.$contentType;
        }

        // Path denylist (admin/auth/settings/etc.). is() ignores the query string.
        if ($request->is(...self::NEVER_CACHE)) {
            return 'path';
        }

        return null;
    }
}
