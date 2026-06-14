<?php

use Illuminate\Support\Facades\Route;

// Public marketing site (home, services, work, etc.)
require __DIR__.'/site.php';

// Admin CMS dashboard + resource management.
require __DIR__.'/admin.php';

// Fortify post-login redirect target -> admin dashboard.
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::redirect('/dashboard', '/admin')->name('dashboard');
});

require __DIR__.'/settings.php';
