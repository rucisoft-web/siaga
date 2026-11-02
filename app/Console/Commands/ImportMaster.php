<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\User;
use App\Services\MasterImportService;
use App\Support\CurrentProperty;
use Illuminate\Console\Command;

class ImportMaster extends Command
{
    protected $signature = 'siaga:import-master {file : Workbook .xlsx format master aset} {--dry-run : Hanya hitung, tidak menyimpan} {--publish : Langsung terbitkan template kamar} {--user= : Email pengguna pelaksana (default: Super Admin pertama)}';

    protected $description = 'Impor master aset (tipe kamar, template isi kamar, ruang meeting, area & item estimasi) dari Excel';

    public function handle(MasterImportService $svc): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error('File tidak ditemukan: '.$file);

            return self::FAILURE;
        }
        app(CurrentProperty::class)->set(Property::orderBy('id')->firstOrFail());
        $user = $this->option('user') ? User::where('email', $this->option('user'))->first()
            : User::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->orderBy('id')->first();
        if (! $user) {
            $this->error('Pengguna pelaksana tidak ditemukan. Jalankan siaga:install dulu.');

            return self::FAILURE;
        }
        $r = $svc->import($file, $user, (bool) $this->option('dry-run'), (bool) $this->option('publish'));
        $this->table(['Data', 'Jumlah'], collect(MasterImportService::LABELS)->map(fn ($l, $k) => [$l, $r['summary'][$k] ?? 0])->values()->all());
        foreach ($r['warnings'] as $w) {
            $this->warn('• '.$w);
        }
        $this->info($r['dry_run'] ? 'Uji coba selesai: belum ada yang disimpan.' : 'Impor selesai.');

        return self::SUCCESS;
    }
}
