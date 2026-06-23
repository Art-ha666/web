<?php

use App\Models\User;

/**
 * @return array<int, string>
 */
function responseCookieNames($response): array
{
    return collect($response->headers->getCookies())
        ->map(fn ($cookie) => $cookie->getName())
        ->all();
}

it('serves anonymous marketing pages cookieless and edge-cacheable', function (string $url) {
    $response = $this->get($url);

    $response->assertOk();

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('public');
    expect($cacheControl)->toContain('s-maxage=300');

    // The session + XSRF cookies must be stripped so Cloudflare can store one
    // shared copy that is identical for every anonymous visitor.
    expect(responseCookieNames($response))
        ->not->toContain(config('session.cookie'))
        ->not->toContain('XSRF-TOKEN');
})->with([
    '/',
    '/services',
    '/work',
    '/process',
    '/about',
    '/team',
    '/insights',
    '/careers',
    '/contact',
]);

it('keeps authenticated pages dynamic with their session cookie', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
    expect((string) $response->headers->get('Cache-Control'))->not->toContain('s-maxage');
    expect(responseCookieNames($response))->toContain(config('session.cookie'));
});

it('keeps the login page dynamic and uncached', function () {
    $response = $this->get('/login');

    $response->assertOk();
    expect((string) $response->headers->get('Cache-Control'))->not->toContain('public');
    expect(responseCookieNames($response))->toContain(config('session.cookie'));
});

it('never edge-caches the contact form submission', function () {
    // POST stays dynamic (GET-only filter), so the redirect keeps its cookie
    // and the success flash survives the round-trip.
    $response = $this->post('/contact', [
        'name' => 'Jane Doe',
        'business_email' => 'jane@acme.com',
        'message' => 'We want to build a real-time settlement platform.',
        'consent_data_processing' => true,
        'website' => '',
    ]);

    $response->assertRedirect();
    expect((string) $response->headers->get('Cache-Control'))->not->toContain('s-maxage');
});
