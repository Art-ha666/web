<?php

use App\Models\SiteSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Scheduled AI blog generation. Runs only when the admin has enabled it in
| Settings and matches the chosen cadence. Generated posts land as drafts
| (unless auto-publish is on) for review.
*/
$aiEnabledFor = fn (string $frequency): callable => function () use ($frequency): bool {
    $settings = SiteSetting::query()->first();

    return $settings !== null
        && $settings->ai_blog_enabled
        && $settings->ai_blog_frequency === $frequency;
};

Schedule::command('blog:generate')->dailyAt('08:00')->when($aiEnabledFor('daily'))->withoutOverlapping();
// Twice a week - Monday (1) and Thursday (4) at 08:00.
Schedule::command('blog:generate')->cron('0 8 * * 1,4')->when($aiEnabledFor('twice_weekly'))->withoutOverlapping();
Schedule::command('blog:generate')->weeklyOn(1, '08:00')->when($aiEnabledFor('weekly'))->withoutOverlapping();
Schedule::command('blog:generate')->monthlyOn(1, '08:00')->when($aiEnabledFor('monthly'))->withoutOverlapping();
