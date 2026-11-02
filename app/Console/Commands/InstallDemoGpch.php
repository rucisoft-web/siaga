<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Data demo GPCH sebagai perusahaan/hotel TERPISAH (kode GPCH): 164 kamar (nomor proyeksi), isi kamar dari master aset,
 * pendataan berjalan, laporan & work order 30 hari, biaya, mutasi, blokir kamar. Untuk situs demo/workshop.
 */
class InstallDemoGpch extends Command
{
    protected $signature = 'siaga:demo-gpch {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Memasang data demo Grand Pasundan (GPCH) sebagai hotel terpisah — untuk situs demo / workshop';

    public function handle(): int
    {
        return $this->call('siaga:demo-data', ['set' => 'gpch', '--force' => (bool) $this->option('force')]);
    }
}
