<?php

use App\Models\Service;

it('renders a branded, themed 404 page', function () {
    $this->get('/a-page-that-does-not-exist')
        ->assertNotFound()
        ->assertSee('AKH Solutions')
        ->assertSee('drifted off course')
        ->assertSee('Back to home');
});

it('renders every branded error view', function (string $view) {
    $html = view("errors.{$view}")->render();

    expect($html)->toContain('AKH Solutions')->toContain('--ak-grad');
})->with(['404', '500', '503', '403', '419', '429']);

it('resolves the AI & Data service at its nav slug (no 404)', function () {
    Service::factory()->create(['slug' => 'ai-and-data', 'is_active' => true]);

    $this->get('/services/ai-and-data')->assertOk();
});
