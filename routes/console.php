<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes (Scheduled Tasks)
|--------------------------------------------------------------------------
|
| Laravel 11: Schedule is defined here using the Schedule facade.
| Commands are auto-discovered from app/Console/Commands/.
|
*/

Schedule::command('update')->everyMinute();

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->describe('Display an inspiring quote');