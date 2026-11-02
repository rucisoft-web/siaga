<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\Property;
use App\Models\Role;
use App\Support\DemoSets;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

/**
 * Data demo per perusahaan TERPISAH (GPCH, Hermina Group, Kimia Farma) untuk situs demo / workshop:
 * struktur, pendataan berjalan, laporan & work order 30 hari, biaya, mutasi, akun jabatan & akun tamu.
 * Data bertanda "(demo)", "contoh", "asumsi", "proyeksi" — bukan data resmi perusahaan.
 */
class InstallDemoData extends Command
{
    protected $signature = 'siaga:demo-data {set=all : gpch | hermina | kimia-farma | all} {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Memasang data demo perusahaan terpisah (GPCH, Hermina Group, Kimia Farma) — untuk situs demo / workshop';

    public function handle(): int
    {
        if (! Property::exists() || ! Role::where('slug', 'super-admin')->exists()) {
            $this->error('SIAGA belum diinstal. Jalankan dulu: php artisan siaga:install');

            return self::FAILURE;
        }
        $set = (string) $this->argument('set');
        $keys = $set === 'all' ? array_keys(DemoSets::SETS) : [$set];
        foreach ($keys as $k) {
            if (! isset(DemoSets::SETS[$k])) {
                $this->error('Data demo tidak dikenal: '.$k.'. Pilihan: '.implode(', ', array_keys(DemoSets::SETS)).', all');

                return self::FAILURE;
            }
        }
        if (! $this->option('force') && ! ($this->input->isInteractive() && confirm('Pasang data demo '.implode(', ', $keys).'? (beberapa menit per perusahaan)', default: false))) {
            return self::SUCCESS;
        }
        foreach ($keys as $k) {
            $class = DemoSets::SETS[$k];
            $about = $class::about();
            $p = Property::where('code', $class::CODE)->first();
            if ($p && Location::withoutGlobalScopes()->where('property_id', $p->id)->exists()) {
                $this->components->warn($about['title'].': data demo sudah ada (lewati). Untuk mengulang, mulai dari basis data bersih.');

                continue;
            }
            $seeder = (new $class)->setContainer(app())->setCommand($this);
            $this->components->task('Data demo '.$about['title'], fn () => $seeder->run() ?? true);
            foreach (array_slice($seeder->messages(), 0, 20) as $m) {
                $this->line('  • '.$m);
            }
            $this->components->twoColumnDetail('Akun demo', $seeder->mail('gm').', '.$seeder->mail('ce').', '.$seeder->mail('sv1').', dst.');
            $this->components->twoColumnDetail('Sandi akun demo', $seeder->password());
        }

        return self::SUCCESS;
    }
}
