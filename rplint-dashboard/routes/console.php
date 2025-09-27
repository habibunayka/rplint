<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
Artisan::command('inspire', function () {
    $this->comment('Stay positive and keep coding!');
})->purpose('Display an inspiring quote');

app(Schedule::class)->command('attendance:auto-check-in')->dailyAt('23:59');
