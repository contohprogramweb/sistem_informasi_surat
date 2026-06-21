<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================
// SCHEDULER UNTUK ARSIP & RETENSI
// ============================================

// Daily: Hapus permanen soft delete > 30 hari dan kirim notifikasi retensi
Schedule::command('arsip:retention-scheduler')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// Daily: Kirim notifikasi retensi (3 bulan sebelum jatuh tempo)
Schedule::command('arsip:retention-scheduler --send-notifications --months-ahead=3')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

// Weekly: Hapus permanen saja (bisa lebih berat prosesnya)
Schedule::command('arsip:retention-scheduler --permanent-delete')
    ->weeklyOn(1, '02:00')
    ->withoutOverlapping()
    ->onOneServer();
