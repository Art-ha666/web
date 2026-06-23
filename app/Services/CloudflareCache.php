<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Purges the Cloudflare edge cache so CMS content changes are visible
 * immediately instead of waiting for the s-maxage TTL to expire.
 *
 * No-ops (returns false) when the API token / zone id are not configured, so
 * local, CI, and test environments never call out to Cloudflare.
 */
class CloudflareCache
{
    public function isConfigured(): bool
    {
        return filled(config('services.cloudflare.token'))
            && filled(config('services.cloudflare.zone_id'));
    }

    /**
     * Purge everything in the zone. Returns true only on a confirmed success.
     */
    public function purgeEverything(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $zone = (string) config('services.cloudflare.zone_id');

        try {
            $response = Http::withToken((string) config('services.cloudflare.token'))
                ->acceptJson()
                ->timeout(10)
                ->post("https://api.cloudflare.com/client/v4/zones/{$zone}/purge_cache", [
                    'purge_everything' => true,
                ]);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        if (! $response->successful() || $response->json('success') !== true) {
            Log::warning('Cloudflare cache purge failed', [
                'status' => $response->status(),
                'errors' => $response->json('errors'),
            ]);

            return false;
        }

        return true;
    }
}
