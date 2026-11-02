<?php

use Illuminate\Support\Facades\Schedule;

// Peringatan SLA (80% → PIC & CE, terlewati → GM). Deduplikasi mencegah notifikasi ganda.
Schedule::command('assetcare:sla-check')->everyMinute()->withoutOverlapping();
