<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar comando para actualizar roles de egresados
// Se ejecuta todos los días a las 2:00 AM
Schedule::command('egresados:actualizar-roles --force')
    ->dailyAt('02:00')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping()
    ->runInBackground();
