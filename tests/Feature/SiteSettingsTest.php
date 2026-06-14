<?php

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

it('saves the analytics, cookie banner, and newsletter settings', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/settings', [
            'site_name' => 'AKH Solutions',
            'nav_cta_label' => 'Book a discovery call',
            'nav_cta_url' => '/contact',
            'ga_measurement_id' => 'G-ABC1234XYZ',
            'head_scripts' => '<script defer data-domain="akh.dav88.dev" src="https://plausible.io/js/script.js"></script>',
            'newsletter_heading' => 'Field notes',
            'newsletter_success' => 'Subscribed - talk soon.',
            'cookie_banner_text' => 'We use a few cookies.',
            'cookie_accept_label' => 'Allow',
            'cookie_decline_label' => 'No thanks',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $settings = SiteSetting::current()->fresh();

    expect($settings->ga_measurement_id)->toBe('G-ABC1234XYZ')
        ->and($settings->newsletter_heading)->toBe('Field notes')
        ->and($settings->cookie_accept_label)->toBe('Allow');
});

it('normalizes em and en dashes in admin input to plain hyphens', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/settings', [
            'site_name' => 'AKH Solutions',
            'nav_cta_label' => 'Book a discovery call',
            'nav_cta_url' => '/contact',
            'tagline' => "Always ahead \u{2014} since 2020",
            'footer_blurb' => "Web \u{2013} AI \u{2013} embedded",
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $settings = SiteSetting::current()->fresh();

    expect($settings->tagline)->toBe('Always ahead - since 2020')
        ->and($settings->footer_blurb)->toBe('Web - AI - embedded');
});

it('rejects a malformed google analytics id', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/settings', [
            'site_name' => 'AKH Solutions',
            'nav_cta_label' => 'Book a discovery call',
            'nav_cta_url' => '/contact',
            'ga_measurement_id' => 'not-a-tag"><script>alert(1)</script>',
        ])
        ->assertSessionHasErrors('ga_measurement_id');
});

it('shares cookie consent config and legal pages with every public page', function () {
    SiteSetting::current()->update(['ga_measurement_id' => 'G-TEST123456']);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('site.cookieConsent.text')
            ->has('site.cookieConsent.acceptLabel')
            ->where('site.cookieConsent.gaId', 'G-TEST123456')
            ->has('site.newsletter.heading')
            ->has('site.legalPages'));
});

it('still stores the lead and reports success when the mailer fails', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->post('/contact', [
        'name' => 'Resilient Lead',
        'business_email' => 'lead@example.com',
        'message' => 'The mailer is down but my lead should still be saved.',
        'consent_data_processing' => true,
        'website' => '',
        'source_page' => '/contact',
    ])->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseHas('leads', ['business_email' => 'lead@example.com']);
});

it('rate limits the public contact endpoint', function () {
    $payload = [
        'name' => 'Spammer',
        'business_email' => 'spam@example.com',
        'message' => 'Spam message body for throttling test.',
        'consent_data_processing' => true,
        'website' => '',
        'source_page' => '/contact',
    ];

    Mail::fake();

    foreach (range(1, 5) as $i) {
        $this->post('/contact', $payload);
    }

    $this->post('/contact', $payload)->assertTooManyRequests();
});
