<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Year rollover: inactivate members who haven't paid for the current year
// Runs daily at midnight; only actually processes on Jan 1
Schedule::command('members:process-year-rollover')->daily();
