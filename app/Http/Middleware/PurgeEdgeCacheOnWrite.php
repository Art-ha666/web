<?php

namespace App\Http\Middleware;

use App\Jobs\PurgeEdgeCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * After any successful admin write (POST/PUT/PATCH/DELETE), purge the Cloudflare
 * edge cache so the change is visible on the public site immediately instead of
 * waiting for the s-maxage TTL. Applied to the admin route group, so it covers
 * every CMS mutation: settings, pages, services, projects, articles, team,
 * jobs, design/theme switches, and homepage content.
 *
 * Over-purging (e.g. on a lead status change that doesn't affect public pages)
 * is harmless - the edge cache simply re-warms - so we keep the rule simple and
 * never risk serving stale content.
 */
class PurgeEdgeCacheOnWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Runs after the response is sent, so the admin's request is never delayed.
     */
    public function terminate(Request $request, Response $response): void
    {
        $isWrite = in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        if ($isWrite && $response->getStatusCode() < 400) {
            PurgeEdgeCache::dispatch();
        }
    }
}
