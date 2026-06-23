<?php

use App\Jobs\PurgeEdgeCache;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('purges the edge cache after a successful admin write', function () {
    Queue::fake();
    $this->actingAs(User::factory()->create());

    $this->post('/admin/services', [
        'title' => 'Purge Test Service',
        'icon' => 'sparkles',
        'benefit_bullets' => ['Ship fast'],
        'tech_stack' => ['Laravel'],
        'gradient' => ['from' => '#111111', 'via' => '#222222', 'to' => '#333333'],
        'is_active' => true,
        'featured' => false,
        'sort_order' => 0,
    ])->assertRedirect()->assertSessionHasNoErrors();

    Queue::assertPushed(PurgeEdgeCache::class);
});

it('does not purge the edge cache on an admin read', function () {
    Queue::fake();
    $this->actingAs(User::factory()->create());

    $this->get('/admin')->assertOk();

    Queue::assertNotPushed(PurgeEdgeCache::class);
});

it('cloudflare purge is a safe no-op when unconfigured', function () {
    Http::fake();
    config()->set('services.cloudflare.token', null);
    config()->set('services.cloudflare.zone_id', null);

    expect(app(CloudflareCache::class)->purgeEverything())->toBeFalse();

    Http::assertNothingSent();
});

it('cloudflare purge calls the api when configured', function () {
    config()->set('services.cloudflare.token', 'test-token');
    config()->set('services.cloudflare.zone_id', 'zone123');
    Http::fake([
        'api.cloudflare.com/*' => Http::response(['success' => true, 'result' => ['id' => 'zone123']]),
    ]);

    expect(app(CloudflareCache::class)->purgeEverything())->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/zones/zone123/purge_cache')
        && $request['purge_everything'] === true
        && $request->hasHeader('Authorization', 'Bearer test-token'));
});
