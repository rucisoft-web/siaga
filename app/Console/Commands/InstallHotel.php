<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Support\Branding;
use App\Support\CurrentProperty;
use Database\Seeders\ReferenceSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Instalasi untuk satu hotel: membuat tabel, profil hotel, peran & data referensi, akun Super Admin,
 tanpa data contoh. Semua isian bisa diubah lagi di Pengaturan › Profil hotel.
 *
 *   php artisan siaga:install                       (tanya jawab)
 *   php artisan siaga:install --hotel="Hotel Melati" --code=HML --timezone=Asia/Makassar \
 *       --admin-email=it@hotelmelati.co.id --admin-password=... --fresh --no-interaction
 *
 * Instalasi selalu BERSIH (tanpa data contoh). Data contoh dipasang terpisah: php artisan siaga:demo
 */
class InstallHotel extends Command
{
    protected $signature = 'siaga:install
        {--hotel= : Nama hotel}
        {--code= : Kode properti / prefiks kode aset (2–8 huruf kapital/angka)}
        {--timezone= : Asia/Jakarta, Asia/Makassar, Asia/Jayapura}
        {--admin-email= : Email Super Admin}
        {--admin-password= : Kata sandi Super Admin}
        {--fresh : Kosongkan seluruh tabel lebih dulu (HAPUS SEMUA DATA)}';

    protected $description = 'Instalasi SIAGA untuk satu hotel (profil, referensi, Super Admin, data contoh opsional)';

    public function handle(): int
    {
        $this->components->info('Instalasi SIAGA');

        $hotel = $this->option('hotel') ?: text('Nama hotel', placeholder: 'mis. Hotel Melati Bandung', required: true);
        $suggest = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', implode('', array_map(fn ($w) => $w[0] ?? '', preg_split('/\s+/', trim($hotel))))), 0, 4)) ?: 'HTL';
        $code = strtoupper((string) ($this->option('code') ?: text('Kode properti (prefiks kode aset, mis. '.$suggest.'-AST-000001)', default: strlen($suggest) >= 2 ? $suggest : 'HTL', required: true)));
        $tz = $this->option('timezone') ?: select('Zona waktu', ['Asia/Jakarta' => 'WIB', 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT'], default: 'Asia/Jakarta');
        $email = $this->option('admin-email') ?: text('Email Super Admin', placeholder: 'mis. it@namahotel.com', required: true);
        $pass = $this->option('admin-password') ?: password('Kata sandi Super Admin (min. 10 karakter, huruf & angka)', required: true);
        $fresh = (bool) $this->option('fresh');

        $v = Validator::make(compact('hotel', 'code', 'tz', 'email', 'pass'), [
            'hotel' => ['required', 'max:120'],
            'code' => ['required', 'regex:/^[A-Z0-9]{2,8}$/'],
            'tz' => ['required', 'timezone'],
            'email' => ['required', 'email'],
            'pass' => [Password::defaults()],
        ], ['code.regex' => 'Kode properti: 2–8 huruf kapital/angka.']);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        $this->components->task($fresh ? 'Membuat ulang tabel (data lama dihapus)' : 'Menjalankan migrasi', fn () => Artisan::call($fresh ? 'migrate:fresh' : 'migrate', ['--force' => true]) === 0);

        // Properti pertama: buat / ganti identitas sebelum referensi di-seed agar seeder memakai kode yang sama.
        $existing = Property::orderBy('id')->first();
        config(['assetcare.skip_default_admin' => true, 'assetcare.property_code' => $code, 'assetcare.hotel_name' => $hotel, 'assetcare.display_timezone' => $tz]);
        if ($existing && $existing->code !== $code) {
            if (\App\Models\Asset::withoutGlobalScopes()->where('property_id', $existing->id)->exists()) {
                $this->error("Sudah ada aset dengan prefiks {$existing->code}. Kode properti tidak bisa diganti; jalankan dengan --code={$existing->code} atau --fresh.");

                return self::FAILURE;
            }
            $existing->update(['code' => $code]);
        }
        $property = Property::updateOrCreate(['code' => $code], ['name' => $hotel, 'timezone' => $tz, 'is_active' => true]);
        if (! $property->app_name) {
            $property->update(['app_name' => config('assetcare.brand_name', 'SIAGA')]);
        }
        Branding::flush();
        app(CurrentProperty::class)->set($property);

        $this->components->task('Peran, izin, departemen, kategori & SLA', fn () => (new ReferenceSeeder)->setContainer(app())->setCommand($this)->__invoke() ?? true);

        $this->components->task('Akun Super Admin', function () use ($email, $pass, $property) {
            $u = User::updateOrCreate(['email' => $email], [
                'name' => 'Super Admin', 'job_title' => 'Super Admin', 'password' => $pass, 'is_active' => true,
                'must_change_password' => false, 'property_id' => $property->id, 'email_verified_at' => now(),
            ]);
            $u->roles()->syncWithoutDetaching([Role::where('slug', 'super-admin')->value('id') => ['property_id' => null]]);

            return true;
        });


        $this->newLine();
        $this->components->twoColumnDetail('Hotel', $hotel);
        $this->components->twoColumnDetail('Kode aset', $code.'-AST-000001');
        $this->components->twoColumnDetail('Zona waktu', $tz);
        $this->components->twoColumnDetail('Super Admin', $email);
        $this->components->info('Selesai. Login di '.url('/admin').' lalu lengkapi logo & warna di Pengaturan › Profil hotel.');
        $this->line('  Data contoh untuk latihan (opsional, bisa kapan saja): php artisan siaga:demo');

        return self::SUCCESS;
    }
}
