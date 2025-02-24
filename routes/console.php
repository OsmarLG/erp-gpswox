<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Programar el comando `services:check` cada hora
Artisan::command('services-check', function () {
    Artisan::call('services:check');
})->purpose('Programar el comando de verificación de servicios')->everyMinute();
