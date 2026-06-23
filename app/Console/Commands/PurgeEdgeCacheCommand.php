<?php

namespace App\Console\Commands;

use App\Services\CloudflareCache;
use Illuminate\Console\Command;

/**
 * Purge the Cloudflare edge cache. Run after each deploy so cached HTML never
 * references the previous build's (now-404) hashed Vite assets.
 */
class PurgeEdgeCacheCommand extends Command
{
    protected $signature = 'cache:purge-edge';

    protected $description = 'Purge the Cloudflare edge cache (no-op if not configured)';

    public function handle(CloudflareCache $cache): int
    {
        if (! $cache->isConfigured()) {
            $this->warn('Cloudflare cache purge skipped: CLOUDFLARE_API_TOKEN / CLOUDFLARE_ZONE_ID not set.');

            return self::SUCCESS;
        }

        if ($cache->purgeEverything()) {
            $this->info('Cloudflare edge cache purged.');

            return self::SUCCESS;
        }

        $this->error('Cloudflare edge cache purge failed (see logs).');

        return self::FAILURE;
    }
}
