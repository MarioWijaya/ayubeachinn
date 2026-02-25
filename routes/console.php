<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app()->booted(function () {
    $schedule = app(Schedule::class);
    $schedule->command('app:auto-cancel-stale-bookings')->hourly();
    $schedule->command('app:auto-start-kamar-perbaikan')->hourly();
    $schedule->command('app:auto-release-kamar-perbaikan')->hourly();
});
