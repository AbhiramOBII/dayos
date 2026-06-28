<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Push Notification Schedule (all times UTC, app timezone is IST UTC+5:30) ──

// 7:00 AM IST = 01:30 UTC — morning motivation
Schedule::command('push:morning-motivation')->dailyAt('01:30')->timezone('UTC');

// 5:00 PM IST = 11:30 UTC — check-in reminder (only fires if user hasn't logged)
Schedule::command('push:checkin-reminder')->dailyAt('11:30')->timezone('UTC');

// 5:00 PM IST = 11:30 UTC — high-value backlog alert (runs alongside check-in)
Schedule::command('push:backlog-alert')->dailyAt('11:30')->timezone('UTC');
