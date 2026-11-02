<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Hanya referensi (izin, peran, departemen, kategori, SLA, Super Admin).
     * Data contoh terpisah: php artisan siaga:demo (atau ASSETCARE_SEED_DEMO=true, dipakai uji otomatis).
     */
    public function run(): void
    {
        $this->call(ReferenceSeeder::class);

        if (config('assetcare.seed_demo')) {
            $this->call(DemoSeeder::class);
        }
    }
}
