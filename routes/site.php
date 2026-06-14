<?php

use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\CareerController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\InsightController;
use App\Http\Controllers\Site\NewsletterController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProcessController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\TeamController;
use App\Http\Controllers\Site\WorkController;
use Illuminate\Support\Facades\Route;

// XML sitemap + robots for search engines (no site.data middleware needed).
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /login',
        'Disallow: /settings',
        '',
        'Sitemap: '.url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
})->name('robots');

/*
| Public marketing site - every route shares site settings + nav via the
| `site.data` middleware so the AppNav and Footer always have their data.
*/
Route::middleware('site.data')->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

    Route::get('/work', [WorkController::class, 'index'])->name('work.index');
    Route::get('/work/{project}', [WorkController::class, 'show'])->name('work.show');

    Route::get('/process', [ProcessController::class, 'index'])->name('process');
    Route::get('/about', [AboutController::class, 'index'])->name('about');
    Route::get('/team', [TeamController::class, 'index'])->name('team');

    Route::get('/insights', [InsightController::class, 'index'])->name('insights.index');
    Route::get('/insights/{article}', [InsightController::class, 'show'])->name('insights.show');

    Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
    Route::get('/careers/{job}', [CareerController::class, 'show'])->name('careers.show');

    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

    Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:10,1')->name('newsletter.store');

    // Legal / system pages (explicit to avoid catch-all conflicts).
    Route::get('/privacy', [PageController::class, 'show'])->defaults('slug', 'privacy')->name('page.privacy');
    Route::get('/cookies', [PageController::class, 'show'])->defaults('slug', 'cookies')->name('page.cookies');
    Route::get('/terms', [PageController::class, 'show'])->defaults('slug', 'terms')->name('page.terms');

    // Arbitrary admin-created CMS pages - {slug} may be a nested path (parent/child).
    Route::get('/page/{slug}', [PageController::class, 'show'])
        ->where('slug', '[A-Za-z0-9_\\-]+(?:/[A-Za-z0-9_\\-]+)*')
        ->name('page.show');
});
