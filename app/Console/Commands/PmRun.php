<?php

namespace App\Console\Commands;

use App\Services\PmService;
use Illuminate\Console\Command;

/** Harian: buat WO dari jadwal PM / inspeksi / kalibrasi yang jatuh tempo, lalu kirim pengingat garansi & kontrak. */
class PmRun extends Command
{
    protected $signature = 'siaga:pm-run {--no-remind : lewati pengingat garansi & kontrak}';

    protected $description = 'Buat work order dari jadwal PM yang jatuh tempo dan kirim pengingat garansi/kontrak (jalankan harian)';

    public function handle(PmService $pm): int
    {
        $made = $pm->runDue();
        $this->info(count($made).' work order PM dibuat'.($made ? ': '.implode(', ', $made) : '.'));
        if (! $this->option('no-remind')) {
            $this->info($pm->remind().' pengingat garansi/kontrak dikirim.');
        }

        return self::SUCCESS;
    }
}
