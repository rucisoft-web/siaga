<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Support\Branding;
use App\Support\CurrentProperty;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ReferenceSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Instalasi untuk satu hotel: membuat tabel, profil hotel, peran & data referensi, akun Super Admin,
 * dan (opsional) data contoh. Semua isian bisa diubah lagi di Pengaturan › Profil hotel.
 *
 *   php artisan assetcare:install                       (tanya jawab)
 *   php artisan assetcare:install --hotel="Hotel Melati" --code=HML --timezone=Asia/Makassar \
 *       --admin-email=it@hotelmelati.co.id --admin-password=... --demo --fresh --no-interaction
 */
class InstallHotel extends Command
{
    protected $signature = 'assetcare:install
        {--hotel= : Nama hotel}
        {--code= : Kode properti / prefiks kode aset (2–8 huruf kapital/angka)}
        {--timezone= : Asia/Jakarta, Asia/Makassar, Asia/Jayapura}
        {--admin-email= : Email Super Admin}
        {--admin-password= : Kata sandi Super Admin}
        {--demo : Isi data contoh (kamar, aset, akun jabatan)}
        {--fresh : Kosongkan seluruh tabel lebih dulu (HAPUS SEMUA DATA)}';

    protected $description = 'Instalasi Hotel Asset Care untuk satu hotel (profil, referensi, Super Admin, data contoh opsional)';

    public function handle(): int
    {
        $this->components->info('Instalasi Hotel Asset Care');

        $hotel = $this->option('hotel') ?: text('Nama hotel', placeholder: 'mis. Hotel Melati Bandung', default: (string) config('assetcare.hotel_name'), required: true);
        $suggest = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', implode('', array_map(fn ($w) => $w[0] ?? '', preg_split('/\s+/', trim($hotel))))), 0, 4)) ?: 'HTL';
        $code = strtoupper((string) ($this->option('code') ?: text('Kode properti (prefiks kode aset, mis. '.$suggest.'-AST-000001)', default: strlen($suggest) >= 2 ? $suggest : 'HTL', required: true)));
        $tz = $this->option('timezone') ?: select('Zona waktu', ['Asia/Jakarta' => 'WIB', 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT'], default: 'Asia/Jakarta');
        $email = $this->option('admin-email') ?: text('Email Super Admin', default: ReferenceSeeder::ADMIN_EMAIL, required: true);
        $pass = $this->option('admin-password') ?: password('Kata sandi Super Admin (min. 10 karakter, huruf & angka)', required: true);
        $demo = $this->option('demo') || ($this->input->isInteractive() && confirm('Isi data contoh untuk mencoba (kamar 301–310, aset, akun jabatan)?', default: true));
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
            $property->update(['app_name' => config('assetcare.brand_name', 'Hotel Asset Care')]);
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

        if ($demo) {
            $this->components->task('Data contoh', fn () => (new DemoSeeder)->setContainer(app())->setCommand($this)->__invoke() ?? true);
        }

        $this->newLine();
        $this->components->twoColumnDetail('Hotel', $hotel);
        $this->components->twoColumnDetail('Kode aset', $code.'-AST-000001');
        $this->components->twoColumnDetail('Zona waktu', $tz);
        $this->components->twoColumnDetail('Super Admin', $email);
        if ($demo) {
            $this->components->twoColumnDetail('Akun contoh', DemoSeeder::email('gm').' dst. / '.DemoSeeder::PASSWORD);
        }
        $this->components->info('Selesai. Login di '.url('/admin').' lalu lengkapi logo & warna di Pengaturan › Profil hotel.');

        return self::SUCCESS;
    }
}
