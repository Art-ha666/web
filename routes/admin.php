<?php

use App\Http\Controllers\Admin\AiWriterController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ClientLogoController;
use App\Http\Controllers\Admin\CtaSectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesignController;
use App\Http\Controllers\Admin\HomeContentController;
use App\Http\Controllers\Admin\JobPostingController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\NavItemController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProcessStepController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Middleware\NormalizeDashes;
use Illuminate\Support\Facades\Route;

/*
| Admin CMS - authenticated management of the public site: switchable
| designs, page builder, content entities, leads inbox, and settings.
| NormalizeDashes rewrites em/en dashes in submitted copy to plain "-".
*/
Route::middleware(['auth', 'verified', NormalizeDashes::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Homepage editor - toggle/reword every homepage section
        Route::get('home', [HomeContentController::class, 'edit'])->name('home.edit');
        Route::put('home', [HomeContentController::class, 'update'])->name('home.update');

        // Page content editor - every section + every string of every page
        Route::get('content', [PageContentController::class, 'index'])->name('content.index');
        Route::get('content/{slug}', [PageContentController::class, 'edit'])->whereAlphaNumeric('slug')->name('content.edit');
        Route::put('content/{slug}', [PageContentController::class, 'update'])->whereAlphaNumeric('slug')->name('content.update');
        Route::put('content/{slug}/reset', [PageContentController::class, 'reset'])->whereAlphaNumeric('slug')->name('content.reset');

        // AI blog writer - settings, provider manager + on-demand generation
        Route::get('ai-writer', [AiWriterController::class, 'index'])->name('ai.index');
        Route::put('ai-writer', [AiWriterController::class, 'update'])->name('ai.update');
        Route::post('ai-writer/generate', [AiWriterController::class, 'generate'])->name('ai.generate');
        Route::post('ai-writer/providers', [AiWriterController::class, 'storeProvider'])->name('ai.providers.store');
        Route::put('ai-writer/providers/{aiProvider}/activate', [AiWriterController::class, 'activateProvider'])->name('ai.providers.activate');
        Route::delete('ai-writer/providers/{aiProvider}', [AiWriterController::class, 'destroyProvider'])->name('ai.providers.destroy');

        // Design / theme switcher + colour customiser
        Route::get('design', [DesignController::class, 'index'])->name('design.index');
        Route::put('design/{theme}/activate', [DesignController::class, 'activate'])->name('design.activate');
        Route::put('design/customize', [DesignController::class, 'customize'])->name('design.customize');
        Route::put('design/reset', [DesignController::class, 'reset'])->name('design.reset');

        // Leads inbox
        Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

        // Site settings
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Content resources
        Route::resource('pages', PageController::class)->except('show');
        Route::resource('services', ServiceController::class)->except('show');
        Route::resource('projects', ProjectController::class)->except('show');
        Route::resource('team', TeamMemberController::class)->parameters(['team' => 'teamMember'])->except('show');
        Route::resource('testimonials', TestimonialController::class)->except('show');
        Route::resource('stats', StatController::class)->except('show');
        Route::resource('process-steps', ProcessStepController::class)->parameters(['process-steps' => 'processStep'])->except('show');
        Route::resource('client-logos', ClientLogoController::class)->parameters(['client-logos' => 'clientLogo'])->except('show');
        Route::resource('articles', ArticleController::class)->except('show');
        Route::resource('jobs', JobPostingController::class)->parameters(['jobs' => 'jobPosting'])->except('show');
        Route::resource('cta-sections', CtaSectionController::class)->parameters(['cta-sections' => 'ctaSection'])->except('show');
        Route::resource('nav-items', NavItemController::class)->parameters(['nav-items' => 'navItem'])->except('show');
    });
