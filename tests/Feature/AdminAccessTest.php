<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests away from the admin', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with([
    '/admin',
    '/admin/home',
    '/admin/content',
    '/admin/content/about',
    '/admin/ai-writer',
    '/admin/design',
    '/admin/pages',
    '/admin/leads',
    '/admin/settings',
    '/admin/services',
    '/admin/projects',
]);

it('lets an authenticated user view the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/Dashboard'));
});

it('lets an authenticated user view the design manager', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/design')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/Design'));
});

it('does not allow public registration', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Intruder',
        'email' => 'intruder@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    expect(User::where('email', 'intruder@example.com')->exists())->toBeFalse();
});
