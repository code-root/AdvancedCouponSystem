<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule sync tasks
Schedule::command('sync:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('sync:reset-daily-counters')
    ->daily()
    ->at('00:00');

// Schedule subscription management tasks
Schedule::job(new \App\Jobs\RotateSyncUsageJob())
    ->daily()
    ->at('01:00')
    ->withoutOverlapping();

Schedule::job(new \App\Jobs\ResetDailyCountersJob())
    ->daily()
    ->at('00:05')
    ->withoutOverlapping();

Schedule::job(new \App\Jobs\NotifyTrialEndingJob())
    ->daily()
    ->at('09:00')
    ->withoutOverlapping();
