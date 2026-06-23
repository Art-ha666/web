<?php

namespace App\Jobs;

use App\Services\CloudflareCache;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Queued Cloudflare edge-cache purge, dispatched whenever an admin changes
 * public content. ShouldBeUnique coalesces a burst of writes (e.g. saving a
 * page that touches several rows) into a single purge within the window.
 */
class PurgeEdgeCache implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Collapse duplicate purge jobs queued within this many seconds.
     */
    public int $uniqueFor = 20;

    public function uniqueId(): string
    {
        return 'purge-edge-cache';
    }

    public function handle(CloudflareCache $cache): void
    {
        $cache->purgeEverything();
    }
}
