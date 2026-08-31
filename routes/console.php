<?php

use App\Services\Settings;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (Schema::hasTable('settings')) {
    $settings = app(Settings::class);

    $backup = $settings->get('backup', Settings::defaults()['backup']);
    $tampilan = $settings->get('tampilan', Settings::defaults()['tampilan']);

    $waktu = $backup['waktu'] ?? '02:00';
    $timezone = $tampilan['zona_waktu'] ?? 'Asia/Jakarta';

    $event = Schedule::command('backup:database')
        ->name('database-backup')
        ->withoutOverlapping()
        ->timezone($timezone);

    match ($backup['jadwal'] ?? 'daily') {
        'weekly' => $event->weeklyOn(Carbon::SUNDAY, $waktu),
        'monthly' => $event->monthlyOn(1, $waktu),
        default => $event->dailyAt($waktu),
    };
}
