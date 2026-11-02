<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Property;
use App\Models\Role;
use App\Models\SlaPolicy;
use App\Models\User;
use App\Services\SlaService;
use App\Support\CurrentProperty;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

/** Seed referensi (aman diulang): properti hotel, izin & peran, departemen, kategori, SLA, Super Admin. */
class ReferenceSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@assetcare.local';

    public const ADMIN_PASSWORD = 'AssetCare@2026';

    public function run(): void
    {
        // Profil hotel dibuat sekali dari .env / assetcare:install; setelah itu diubah lewat Pengaturan › Profil hotel.
        $property = Property::firstOrCreate(['code' => config('assetcare.property_code', 'HTL')], [
            'name' => config('assetcare.hotel_name', 'Hotel Contoh'), 'timezone' => config('assetcare.display_timezone', 'Asia/Jakarta'),
            'app_name' => config('assetcare.brand_name', 'Hotel Asset Care'), 'is_active' => true,
        ]);
        app(CurrentProperty::class)->set($property);

        // izin & peran
        foreach (PermissionCatalog::PERMISSIONS as $slug => [$name, $group]) {
            Permission::updateOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group]);
        }
        $allPerms = Permission::pluck('id', 'slug');
        foreach (PermissionCatalog::ROLES as $slug => [$name, $desc, $perms]) {
            $role = Role::updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $desc]);
            $role->permissions()->sync($perms === ['*'] ? $allPerms->values() : $allPerms->only($perms)->values());
        }

        $departments = [
            'EXE' => 'Executive Office', 'FO' => 'Front Office', 'HK' => 'Housekeeping', 'FB' => 'Food & Beverage Service',
            'KIT' => 'Kitchen & Pastry', 'ENG' => 'Engineering', 'SM' => 'Sales & Marketing', 'ACC' => 'Accounting & Finance',
            'HR' => 'Human Resources', 'PUR' => 'Purchasing', 'EDP' => 'EDP / IT', 'SEC' => 'Security',
        ];
        foreach ($departments as $code => $name) {
            Department::withoutGlobalScopes()->updateOrCreate(['property_id' => $property->id, 'code' => $code], ['name' => $name, 'is_active' => true]);
        }

        // kategori per kelompok objek hotel
        $cats = [
            // kode, nama, kelompok, kritikalitas, mode, interval PM usulan (hari)
            ['AC-SPLIT', 'AC split kamar', 'kamar', 'medium', 'individual', 90], ['TV', 'Televisi', 'kamar', 'low', 'individual', null],
            ['MINIBAR', 'Minibar / kulkas kamar', 'kamar', 'low', 'individual', 180], ['TEL', 'Telepon kamar', 'kamar', 'low', 'individual', null],
            ['BED', 'Tempat tidur', 'kamar', 'low', 'individual', 365], ['LAMP', 'Lampu meja / baca', 'kamar', 'low', 'individual', null],
            ['SAFE', 'Safe deposit box kamar', 'kamar', 'medium', 'individual', 365], ['DOORLOCK', 'Door lock elektronik', 'kamar', 'high', 'individual', 180],
            ['FURN', 'Furniture kamar', 'kamar', 'low', 'individual', null], ['SANIT', 'Sanitary & perlengkapan mandi', 'kamar', 'medium', 'individual', 180],
            ['LINEN', 'Linen & handuk', 'kamar', 'low', 'quantity', null],
            ['PROJ', 'Projector', 'mice', 'medium', 'individual', 180], ['SCREEN', 'Screen', 'mice', 'low', 'individual', null],
            ['SOUND', 'Sound system', 'mice', 'medium', 'individual', 180], ['MIC', 'Microphone', 'mice', 'low', 'individual', null],
            ['CHAIR-B', 'Kursi banquet', 'mice', 'low', 'quantity', null], ['TABLE-B', 'Meja banquet', 'mice', 'low', 'quantity', null],
            ['PART', 'Partisi ruang', 'mice', 'medium', 'individual', 365], ['PANEL-MICE', 'Panel listrik venue', 'mice', 'high', 'individual', 180],
            ['CHILL', 'Chiller kitchen', 'kitchen', 'high', 'individual', 90], ['FREEZ', 'Freezer', 'kitchen', 'high', 'individual', 90],
            ['STOVE', 'Kompor / burner', 'kitchen', 'high', 'individual', 90], ['OVEN', 'Oven', 'kitchen', 'medium', 'individual', 180],
            ['MIXER', 'Mixer pastry', 'kitchen', 'medium', 'individual', 180], ['EXHAUST', 'Exhaust hood', 'kitchen', 'high', 'individual', 90],
            ['GREASE', 'Grease trap', 'kitchen', 'medium', 'individual', 30], ['GAS', 'Instalasi gas', 'kitchen', 'critical', 'individual', 30],
            ['BOILER', 'Boiler', 'mep', 'critical', 'individual', 30], ['PUMP', 'Pompa', 'mep', 'high', 'individual', 90],
            ['TANK', 'Tangki air', 'mep', 'medium', 'individual', 180], ['GENSET', 'Genset', 'mep', 'critical', 'individual', 30],
            ['PANEL', 'Panel listrik', 'mep', 'critical', 'individual', 90], ['LIFT', 'Lift', 'mep', 'critical', 'individual', 30],
            ['HVAC', 'HVAC sentral / AHU', 'mep', 'high', 'individual', 90],
            ['POOL-EQ', 'Peralatan kolam renang', 'publik', 'medium', 'individual', 30], ['GYM-EQ', 'Peralatan gym', 'publik', 'low', 'individual', 90],
            ['APAR', 'APAR', 'keselamatan', 'critical', 'individual', 30], ['ALARM', 'Panel fire alarm', 'keselamatan', 'critical', 'individual', 30],
            ['DETECT', 'Detektor asap / panas', 'keselamatan', 'critical', 'individual', 180], ['HYDRANT', 'Hydrant', 'keselamatan', 'critical', 'individual', 90],
            ['EMLIGHT', 'Emergency light', 'keselamatan', 'high', 'individual', 90],
            ['SERVER', 'Server', 'it', 'critical', 'individual', 90], ['AP', 'Access point', 'it', 'medium', 'individual', null],
            ['CCTV', 'CCTV', 'it', 'high', 'individual', 90], ['SWITCH', 'Network switch', 'it', 'high', 'individual', null],
            ['PABX', 'PABX', 'it', 'high', 'individual', 180], ['PC', 'PC / laptop', 'it', 'low', 'individual', null],
            ['PRINTER', 'Printer', 'it', 'low', 'individual', null], ['ACCESS', 'Access control', 'it', 'high', 'individual', 180],
        ];
        foreach ($cats as [$code, $name, $group, $crit, $mode, $pm]) {
            AssetCategory::withoutGlobalScopes()->updateOrCreate(['property_id' => $property->id, 'code' => $code], [
                'name' => $name, 'group' => $group, 'default_criticality' => $crit, 'tracking_mode' => $mode, 'default_pm_interval_days' => $pm, 'is_active' => true,
            ]);
        }

        foreach (SlaService::DEFAULTS as $prio => [$name, $example, $resp, $rec, $cal]) {
            SlaPolicy::withoutGlobalScopes()->firstOrCreate(['property_id' => $property->id, 'priority' => $prio], [
                'name' => $name, 'example' => $example, 'response_minutes' => $resp, 'recovery_minutes' => $rec, 'calendar' => $cal,
            ]);
        }

        // Super Admin awal hanya dibuat bila belum ada satu pun (aman saat seeder diulang pada rilis pembaruan).
        // assetcare:install membuat Super Admin sendiri (email & sandi diisi saat instalasi).
        $superRole = Role::where('slug', 'super-admin')->value('id');
        if (config('assetcare.skip_default_admin') || User::whereHas('roles', fn ($q) => $q->where('roles.id', $superRole))->exists()) {
            return;
        }
        // Lokal: sandi tetap untuk uji coba. Produksi: ASSETCARE_ADMIN_PASSWORD atau sandi acak yang ditampilkan sekali.
        $prod = app()->isProduction();
        $pass = $prod ? (env('ASSETCARE_ADMIN_PASSWORD') ?: 'AC-'.\Illuminate\Support\Str::password(14, symbols: false)) : self::ADMIN_PASSWORD;
        $admin = User::firstOrCreate(['email' => self::ADMIN_EMAIL], [
            'name' => 'Super Admin', 'job_title' => 'Super Admin (pengelola aplikasi)', 'password' => $pass,
            'property_id' => $property->id, 'is_active' => true, 'must_change_password' => $prod,
        ]);
        if ($admin->wasRecentlyCreated && $prod && $this->command) {
            $this->command->warn('Super Admin: '.self::ADMIN_EMAIL.'  /  '.$pass.'  (CATAT sekarang; wajib diganti saat login pertama)');
        }
        $admin->roles()->syncWithoutDetaching([$superRole => ['property_id' => null]]);
    }
}
