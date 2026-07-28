<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Penjadwalan Otomatis Harian ─────────────────────────────────────────────

// Setiap hari jam 01:00 dini hari: Perbarui kurs mata uang & risk score semua negara
Schedule::command('risk:update')->dailyAt('01:00')->withoutOverlapping();

// Setiap hari jam 02:00 dini hari: Isi data ekonomi negara yang belum lengkap
Schedule::command('economic:fill')->dailyAt('02:00')->withoutOverlapping();

// Setiap Minggu jam 03:00 dini hari: Sinkronisasi ulang daftar negara dari RestCountries API
Schedule::command('sync:countries --force')->weeklyOn(0, '03:00')->withoutOverlapping();
