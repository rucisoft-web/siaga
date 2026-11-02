<?php

use Illuminate\Support\Facades\Schedule;

// Peringatan SLA (80% → PIC & CE, terlewati → GM). Deduplikasi mencegah notifikasi ganda.
Schedule::command('siaga:sla-check')->everyMinute()->withoutOverlapping();

// Jadwal PM / inspeksi / kalibrasi → WO otomatis, plus pengingat garansi & kontrak (H-30, H-7).
Schedule::command('siaga:pm-run')->dailyAt('06:00')->timezone(config('assetcare.display_timezone', 'Asia/Jakarta'))->withoutOverlapping();
