<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class Doctor extends Command
{
    protected $signature = 'siaga:doctor';

    protected $description = 'Pemeriksaan konfigurasi, basis data, izin & peran SIAGA';

    public function handle(): int
    {
        $rows = [];
        $add = function (string $item, bool $ok, string $info = '') use (&$rows) {
            $rows[] = [$ok ? '<fg=green>OK</>' : '<fg=red>PERIKSA</>', $item, $info];
        };
        $add('PHP >= 8.3', version_compare(PHP_VERSION, '8.3.0', '>='), PHP_VERSION);
        foreach (['pdo_mysql', 'mbstring', 'intl', 'gd', 'zip', 'fileinfo', 'openssl'] as $ext) {
            $add('Ekstensi '.$ext, extension_loaded($ext), extension_loaded($ext) ? '' : 'aktifkan di php.ini (Laragon: Menu › PHP › Extensions)');
        }
        $add('APP_KEY terisi', filled(config('app.key')), 'php artisan key:generate');
        $add('APP_DEBUG mati di produksi', ! (app()->isProduction() && config('app.debug')), 'APP_ENV='.app()->environment().', APP_DEBUG='.(config('app.debug') ? 'true' : 'false'));
        $add('Waktu tersimpan UTC', config('app.timezone') === 'UTC', config('app.timezone').' (tampilan: '.\App\Support\Fmt::tz().')');
        $add('URL QR', filled(config('assetcare.qr_base_url')), config('assetcare.qr_base_url').' — URL ini tercetak permanen di label');
        try {
            DB::connection()->getPdo();
            $ver = DB::selectOne('select version() v')->v ?? '?';
            $add('Koneksi basis data', true, config('database.default').' / '.DB::connection()->getDatabaseName().' (MySQL '.$ver.')');
            $add('MySQL 8.x', version_compare($ver, '8.0.0', '>='), $ver);
        } catch (Throwable $e) {
            $add('Koneksi basis data', false, $e->getMessage());
            $this->table(['Status', 'Pemeriksaan', 'Keterangan'], $rows);

            return self::FAILURE;
        }
        $add('Storage dapat ditulis', is_writable(storage_path('app')) && is_writable(storage_path('logs')));
        $add('Aset Filament terpublikasi', file_exists(public_path('js/filament/filament/app.js')), 'php artisan filament:assets');
        $add('Ekstensi GD (QR & PDF)', extension_loaded('gd'));
        $missing = array_diff(array_keys(PermissionCatalog::PERMISSIONS), Permission::pluck('slug')->all());
        $add('Izin lengkap ('.count(PermissionCatalog::PERMISSIONS).')', $missing === [], $missing ? 'kurang: '.implode(', ', $missing).' → php artisan db:seed' : '');
        $add('Peran ('.count(PermissionCatalog::ROLES).')', Role::count() >= count(PermissionCatalog::ROLES), Role::count().' peran');
        $add('Properti', Property::exists(), Property::pluck('code')->join(', '));
        $add('Super Admin tersedia', User::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->exists(), 'php artisan siaga:create-admin');
        $noRole = User::where('is_active', true)->whereDoesntHave('roles')->pluck('email');
        $add('Pengguna aktif punya peran', $noRole->isEmpty(), $noRole->join(', '));
        $add('Jadwal (cron) untuk SLA', true, '* * * * * php artisan schedule:run  (Laragon: jalankan "php artisan schedule:work")');
        $this->table(['Status', 'Pemeriksaan', 'Keterangan'], $rows);

        return self::SUCCESS;
    }
}
