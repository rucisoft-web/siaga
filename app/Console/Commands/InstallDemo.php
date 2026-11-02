<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\Role;
use App\Support\CurrentProperty;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

/**
 * Data contoh untuk latihan / demo, TERPISAH dari instalasi.
 * Menambah: gedung, lantai 1 & 3, kamar 301–310 (Deluxe), area kitchen/ballroom/MEP, template TPL-DLX,
 * 10 aset contoh, 2 vendor, batas persetujuan contoh, dan 16 akun jabatan @ASSETCARE_DEMO_EMAIL_DOMAIN.
 * Aman diulang (tidak menggandakan). Untuk data asli, sebaiknya pakai instalasi/database terpisah.
 */
class InstallDemo extends Command
{
    protected $signature = 'siaga:demo {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Memasang data contoh SIAGA (kamar 301–310, aset & akun jabatan contoh) — opsional, untuk latihan';

    public function handle(): int
    {
        $p = Property::orderBy('id')->first();
        if (! $p || ! Role::where('slug', 'super-admin')->exists()) {
            $this->error('SIAGA belum diinstal. Jalankan dulu: php artisan siaga:install');

            return self::FAILURE;
        }

        $this->components->warn("Data contoh akan DITAMBAHKAN ke {$p->name} ({$p->code}): kamar 301–310, aset contoh, akun @".config('assetcare.demo_email_domain').'.');
        $this->line('  Data contoh bercampur dengan data asli dan tidak dihapus otomatis. Untuk latihan, sebaiknya pakai instalasi/database terpisah.');
        if (! $this->option('force') && ! ($this->input->isInteractive() && confirm('Lanjutkan memasang data contoh?', default: false))) {
            $this->line('Dibatalkan.');

            return self::SUCCESS;
        }

        config(['assetcare.property_code' => $p->code]);
        app(CurrentProperty::class)->set($p);
        $this->components->task('Data contoh', fn () => (new DemoSeeder)->setContainer(app())->setCommand($this)->__invoke() ?? true);
        $this->components->twoColumnDetail('Akun contoh', DemoSeeder::email('gm').', '.DemoSeeder::email('teknisi1').', dst.');
        $this->components->twoColumnDetail('Sandi akun contoh', DemoSeeder::PASSWORD.' (wajib diganti saat login)');

        return self::SUCCESS;
    }
}
