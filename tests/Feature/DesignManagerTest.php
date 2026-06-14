<?php

use App\Models\SiteSetting;
use App\Models\Subscriber;
use App\Models\Theme;
use App\Models\User;
use App\Services\ThemeService;

it('activates a different design', function () {
    $user = User::factory()->create();
    $aurora = Theme::factory()->create(['key' => 'aurora']);
    $quantum = Theme::factory()->create(['key' => 'quantum', 'hero_variant' => 'quantum']);
    SiteSetting::current()->update(['active_theme_id' => $aurora->id]);

    $this->actingAs($user)
        ->put("/admin/design/{$quantum->id}/activate")
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(SiteSetting::current()->active_theme_id)->toBe($quantum->id);
    expect(app(ThemeService::class)->meta()['key'])->toBe('quantum');
});

it('customises and resets the colour palette', function () {
    $user = User::factory()->create();
    $theme = Theme::factory()->create(['key' => 'aurora', 'tokens' => ['primary' => '#5778f8', 'scheme' => 'dark']]);
    SiteSetting::current()->update(['active_theme_id' => $theme->id]);

    $this->actingAs($user)
        ->put('/admin/design/customize', ['tokens' => ['primary' => '#ff0000']])
        ->assertRedirect();

    expect(SiteSetting::current()->custom_tokens)->toMatchArray(['primary' => '#ff0000']);
    expect(app(ThemeService::class)->active()['tokens']['primary'])->toBe('#ff0000');

    $this->actingAs($user)->put('/admin/design/reset')->assertRedirect();
    expect(SiteSetting::current()->fresh()->custom_tokens)->toBeNull();
});

it('captures newsletter subscribers', function () {
    $this->post('/newsletter', ['email' => 'fan@example.com'])->assertRedirect();
    $this->assertDatabaseHas('subscribers', ['email' => 'fan@example.com']);

    // honeypot
    $this->post('/newsletter', ['email' => 'bot@example.com', 'company' => 'spam']);
    expect(Subscriber::where('email', 'bot@example.com')->exists())->toBeFalse();
});
