<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\RoomType;

/**
 * DATA DEMO "Hermina Group" — SATU perusahaan dengan DUA lokasi rumah sakit: RS Hermina Kemayoran & RS Hermina Bekasi.
 *
 * Fakta dari Laporan Tahunan 2025 PT Medikaloka Hermina Tbk: alamat, tahun berdiri (Kemayoran 1995, Bekasi 1997),
 * kelas B, akreditasi Paripurna; grup 52 RS / 8.666 TT (rata-rata ±167 TT per RS); layanan unggulan Kemayoran:
 * Rehabilitation Robotic Center & Sports Clinic.
 * PROYEKSI / ASUMSI (agar semua fitur terlihat): jumlah & sebaran kamar rawat inap per kelas, isi standar kamar,
 * alat medis per instalasi, nomor kamar, hasil pendataan, laporan kerusakan, work order, biaya, kalibrasi, mutasi.
 */
class HerminaDemoSeeder extends ProjectedDemoSeeder
{
    public const CODE = 'HRM';

    public const PASSWORD = 'Hermina-2026!demo';

    public const KMY = 'RS Hermina Kemayoran';

    public const BKS = 'RS Hermina Bekasi';

    public function code(): string
    {
        return self::CODE;
    }

    public function password(): string
    {
        return self::PASSWORD;
    }

    public static function about(): array
    {
        return [
            'title' => 'Hermina Group', 'sector' => 'Rumah sakit · 2 badan usaha, 2 RS (Jakarta & Bekasi)',
            'summary' => 'Contoh grup dengan beberapa badan usaha: PT wilayah Jakarta mengelola RS Hermina Kemayoran, PT wilayah Bekasi mengelola RS Hermina Bekasi (kelas B): kamar rawat inap VVIP–kelas 3 & isolasi, IGD, ICU, NICU, bedah sentral, radiologi, hemodialisa, rehabilitasi robotik. Alat medis, kalibrasi, cold chain farmasi, gas medis.',
            'source' => 'Laporan Tahunan 2025 Hermina (alamat, kelas, akreditasi, layanan unggulan). Jumlah TT, isi kamar, alat medis & riwayat perbaikan = proyeksi.',
        ];
    }

    protected function propertyInfo(): array
    {
        return ['business_type' => 'hospital', 'name' => 'Hermina Group (demo)', 'city' => 'Jakarta & Bekasi', 'star_rating' => null, 'room_count' => count($this->roomList()), 'primary_color' => '#00775A', 'accent_color' => '#E3A008'];
    }

    protected function departments(): ?array
    {
        return ['EXE' => 'Direksi', 'ENG' => 'IPSRS (Pemeliharaan Sarana)', 'HK' => 'Keperawatan', 'FO' => 'Admisi & Rawat Inap', 'FB' => 'Penunjang Medis',
            'ACC' => 'Keuangan & Akuntansi', 'LOG' => 'Logistik & Farmasi', 'IT' => 'SIMRS / IT', 'SEC' => 'K3RS & Keamanan'];
    }

    protected function vendors(): array
    {
        return [
            ['V-EM', 'PT Elektromedika Servis (asumsi)', 'Servis & kalibrasi alat medis', 'Kontrak tahunan, respons 4 jam (alat kritis)'],
            ['V-KAL', 'Laboratorium Kalibrasi Terakreditasi (asumsi)', 'Kalibrasi alat ukur medis', 'Jadwal tahunan'],
            ['V-GAS', 'PT Gas Medis Nusantara (asumsi)', 'Oksigen cair, manifold, instalasi gas medis', '24 jam'],
            ['V-HVAC', 'CV Sejuk Teknik (asumsi)', 'AC, chiller, AHU kamar operasi', '24 jam / 4 jam respons'],
            ['V-GEN', 'PT Daya Genset Servis (asumsi)', 'Genset & panel listrik', 'Kontrak tahunan, respons 6 jam'],
        ];
    }

    protected function people(): array
    {
        return [
            'gm' => ['Direktur Operasional Grup (contoh)', 'Direktur Operasional', 'EXE', ['gm']],
            'ce' => ['Kepala IPSRS (contoh)', 'Kepala Instalasi Pemeliharaan Sarana RS', 'ENG', ['chief-engineering']],
            'ac' => ['Admin Aset IPSRS (contoh)', 'Admin Aset / Inventaris', 'ENG', ['asset-controller']],
            'tek1' => ['Teknisi Elektromedis (contoh)', 'Teknisi Elektromedis', 'ENG', ['technician']],
            'tek2' => ['Teknisi Listrik & Genset (contoh)', 'Teknisi IPSRS', 'ENG', ['technician']],
            'tek3' => ['Teknisi Sipil, Plumbing & HVAC (contoh)', 'Teknisi IPSRS', 'ENG', ['technician']],
            'fin' => ['Manajer Keuangan (contoh)', 'Manajer Keuangan', 'ACC', ['finance']],
            'qc' => ['Komite Mutu / SPI (contoh)', 'Satuan Pengawas Internal', 'ACC', ['auditor']],
            'sv1' => ['Petugas Pendataan Kemayoran 1 (contoh)', 'Petugas pendataan', 'ENG', ['surveyor']],
            'sv2' => ['Petugas Pendataan Kemayoran 2 (contoh)', 'Petugas pendataan', 'HK', ['surveyor']],
            'sv3' => ['Petugas Pendataan Bekasi 1 (contoh)', 'Petugas pendataan', 'ENG', ['surveyor']],
            'sv4' => ['Petugas Pendataan Bekasi 2 (contoh)', 'Petugas pendataan', 'HK', ['surveyor']],
        ];
    }

    protected function peopleExtra(): array
    {
        return [
            'fo' => ['Kepala Ruang Rawat Inap (contoh)', 'Kepala Ruang', 'FO', ['reporter', 'front-office']],
            'fom' => ['Manajer Keperawatan (contoh)', 'Manajer Keperawatan', 'HK', ['hod', 'front-office']],
            'hk' => ['Perawat Penanggung Jawab Shift (contoh)', 'Perawat PJ Shift', 'HK', ['reporter']],
            'fbm' => ['Kepala Instalasi Penunjang Medis (contoh)', 'Kepala Instalasi', 'FB', ['hod']],
        ];
    }

    protected function custody(bool $technical): array
    {
        return $technical ? ['ENG', 'IPSRS'] : ['HK', 'Keperawatan'];
    }

    protected function warehouseName(): string
    {
        return 'Gudang IPSRS';
    }

    protected function blockReason(): string
    {
        return 'Perbaikan, kamar rawat tidak dapat dipakai sementara (pasien dipindah)';
    }

    protected function unitThreshold(): int
    {
        return 20;
    }

    protected function blankStock(): int
    {
        return 360;
    }

    protected function engCategoryNames(): array
    {
        return ['Alat medis', 'Elektronik', 'Nurse call & gas medis', 'Kamar mandi', 'Keselamatan', 'HVAC isolasi'];
    }

    protected function brands(): array
    {
        return ['Bed pasien elektrik' => ['Paramount Bed', 'A5 series'], 'Patient monitor' => ['Mindray', 'uMEC 10'], 'Infusion pump' => ['Terumo', 'TE-171'],
            'AC split' => ['Daikin', 'FTKC25'], 'Televisi' => ['LG', '32LQ63'], 'Flowmeter O2 + humidifier' => ['Precision Medical', 'PM1500']];
    }

    protected function extras(): array
    {
        return [['Kursi roda', 'Furniture pasien'], ['Tiang infus tambahan', 'Furniture pasien'], ['Lampu periksa portable', 'Alat medis']];
    }

    protected function techMap(): array
    {
        return ['alat_medis' => 'tek1', 'ac' => 'tek3', 'listrik' => 'tek2', 'air' => 'tek3', 'elektronik' => 'tek2', 'furniture' => 'tek3', 'kitchen' => 'tek3',
            'keselamatan' => 'tek2', 'it' => 'tek2', 'mice' => 'tek2', 'bangunan' => 'tek3', 'lainnya' => 'tek2', 'lift' => 'tek2'];
    }

    // ------------------------------------------------------------------ struktur (PROYEKSI)

    protected function roomTypes(): array
    {
        return ['VVIP' => self::KMY, 'VIP' => self::KMY, 'Kelas 1' => self::KMY, 'Kelas 2' => self::KMY, 'Kelas 3' => self::KMY, 'Isolasi' => self::KMY];
    }

    protected function afterImport(): void
    {
        $this->legalEntities();
        foreach (['VVIP' => '1 TT + ruang keluarga', 'VIP' => '1 TT', 'Kelas 1' => '2 TT', 'Kelas 2' => '3 TT', 'Kelas 3' => '6 TT', 'Isolasi' => '1 TT, tekanan negatif'] as $n => $bed) {
            RoomType::where('name', $n)->update(['bed_config' => $bed]);
        }
    }

    /**
     * Satu grup bisa terdiri dari beberapa badan usaha (PT) yang mengelola RS di daerah berbeda.
     * Nama PT = CONTOH, bukan nama badan hukum sebenarnya.
     */
    protected function entities(): array
    {
        return [
            ['PT-JKT', 'PT RS Hermina Wilayah Jakarta (contoh)', [self::KMY]],
            ['PT-BKS', 'PT RS Hermina Wilayah Bekasi (contoh)', [self::BKS]],
        ];
    }

    public function legalEntities(): void
    {
        foreach ($this->entities() as [$code, $name, $buildings]) {
            $pt = Location::firstOrCreate(['property_id' => $this->p->id, 'code' => $code], ['name' => $name, 'location_type' => 'company', 'is_active' => true]);
            Location::whereIn('name', $buildings)->whereNull('parent_id')->get()->each(fn ($b) => $b->update(['parent_id' => $pt->id, 'location_type' => 'building']));
        }
        // ruang/area yang tidak berada di bawah gedung RS (mis. gudang pusat) tetap di tingkat teratas
    }

    protected function roomItems(): array
    {
        //                                                                  VVIP VIP K1 K2 K3 Iso
        return [
            ['Alat medis', 'Bed pasien elektrik', 'unit', [1, 1, 2, 3, 6, 1]],
            ['Alat medis', 'Flowmeter O2 + humidifier', 'unit', [1, 1, 2, 3, 6, 1]],
            ['Alat medis', 'Suction regulator dinding', 'unit', [1, 1, 1, 1, 2, 1]],
            ['Alat medis', 'Tensimeter digital dinding', 'unit', [1, 1, 1, 1, 1, 1]],
            ['Alat medis', 'Patient monitor', 'unit', [0, 0, 0, 0, 0, 1]],
            ['Alat medis', 'Infusion pump', 'unit', [0, 0, 0, 0, 0, 1]],
            ['Furniture pasien', 'Bedside cabinet', 'unit', [1, 1, 2, 3, 6, 1]],
            ['Furniture pasien', 'Overbed table', 'unit', [1, 1, 2, 3, 0, 1]],
            ['Furniture pasien', 'Tiang infus', 'unit', [1, 1, 2, 3, 6, 1]],
            ['Furniture pasien', 'Kursi penunggu', 'unit', [0, 0, 2, 3, 6, 1]],
            ['Furniture pasien', 'Sofa bed penunggu', 'unit', [1, 1, 0, 0, 0, 0]],
            ['Furniture pasien', 'Sofa tamu set', 'unit', [1, 0, 0, 0, 0, 0]],
            ['Furniture pasien', 'Lemari pakaian', 'unit', [1, 1, 1, 1, 2, 0]],
            ['Furniture pasien', 'Tirai pemisah bed', 'set', [0, 0, 1, 2, 5, 0]],
            ['Elektronik', 'AC split', 'unit', [2, 1, 1, 1, 2, 0]],
            ['Elektronik', 'Televisi', 'unit', [2, 1, 1, 1, 1, 1]],
            ['Elektronik', 'Kulkas kamar', 'unit', [1, 1, 0, 0, 0, 0]],
            ['Elektronik', 'Water heater', 'unit', [1, 1, 1, 0, 0, 1]],
            ['Nurse call & gas medis', 'Nurse call bedhead', 'titik', [1, 1, 2, 3, 6, 1]],
            ['Nurse call & gas medis', 'Outlet gas medis (O2 / vakum)', 'titik', [2, 2, 4, 6, 12, 2]],
            ['Kamar mandi', 'Kloset duduk', 'unit', [1, 1, 1, 1, 1, 1]],
            ['Kamar mandi', 'Wastafel', 'unit', [2, 1, 1, 1, 1, 1]],
            ['Kamar mandi', 'Shower set', 'unit', [1, 1, 1, 1, 1, 1]],
            ['Kamar mandi', 'Handrail kamar mandi', 'set', [1, 1, 1, 1, 1, 1]],
            ['Keselamatan', 'Smoke detector', 'unit', [2, 1, 1, 1, 1, 1]],
            ['Keselamatan', 'Hand rub dispenser', 'pcs', [2, 2, 2, 3, 6, 2]],
            ['HVAC isolasi', 'Exhaust fan HEPA (tekanan negatif)', 'unit', [0, 0, 0, 0, 0, 1]],
            ['Linen', 'Linen set pasien', 'set', [3, 3, 6, 9, 18, 4]],
        ];
    }

    protected function layout(): array
    {
        return [
            self::KMY => ['prefix' => 'K', 'floors' => [
                3 => ['Kelas 3' => 8, 'Isolasi' => 4], 4 => ['Kelas 2' => 14], 5 => ['Kelas 1' => 16], 6 => ['Kelas 1' => 10, 'VIP' => 6], 7 => ['VIP' => 10, 'VVIP' => 4],
            ]],
            self::BKS => ['prefix' => 'B', 'floors' => [
                2 => ['Kelas 3' => 9], 3 => ['Kelas 2' => 14], 4 => ['Kelas 1' => 12, 'Isolasi' => 4], 5 => ['Kelas 1' => 10, 'VIP' => 6], 6 => ['VIP' => 6, 'VVIP' => 2],
            ]],
        ];
    }

    protected function venues(): array
    {
        return [['Aula Kemayoran', self::KMY], ['Aula Bekasi', self::BKS]];
    }

    protected function areaItems(): array
    {
        $rows = [];
        foreach ([self::KMY => 1.0, self::BKS => 0.85] as $rs => $f) {
            $k = $rs === self::KMY;
            $n = fn (int $q) => max(1, (int) round($q * $f));
            $areas = [
                'IGD' => [['Brankar IGD', 'Alat medis', $n(10)], ['Defibrillator', 'Alat medis', 2], ['Patient monitor', 'Alat medis', $n(6)], ['Ventilator transport', 'Alat medis', 1],
                    ['Suction portable', 'Alat medis', 3], ['EKG 12 lead', 'Alat medis', 2], ['Infusion pump', 'Alat medis', $n(6)], ['Syringe pump', 'Alat medis', $n(6)], ['Troli emergensi', 'Alat medis', 3], ['Nebulizer', 'Alat medis', 4]],
                'Instalasi Bedah Sentral' => [['Meja operasi', 'Alat medis', $n(4)], ['Lampu operasi', 'Alat medis', $n(4)], ['Mesin anestesi', 'Alat medis', $n(4)], ['Electrosurgical unit', 'Alat medis', $n(4)],
                    ['Patient monitor', 'Alat medis', $n(4)], ['Suction unit', 'Alat medis', $n(4)], ['C-arm', 'Alat medis', 1], ['AHU kamar operasi', 'HVAC & utilitas', $n(4)]],
                'ICU' => [['Bed ICU', 'Alat medis', $n(8)], ['Ventilator', 'Alat medis', $n(6)], ['Patient monitor', 'Alat medis', $n(8)], ['Central monitor', 'Alat medis', 1],
                    ['Syringe pump', 'Alat medis', $n(16)], ['Infusion pump', 'Alat medis', $n(12)], ['Defibrillator', 'Alat medis', 1], ['Blood gas analyzer', 'Alat medis', 1]],
                'Perinatologi & NICU' => [['Inkubator', 'Alat medis', $n(8)], ['Infant warmer', 'Alat medis', 4], ['CPAP bayi', 'Alat medis', 3], ['Phototherapy unit', 'Alat medis', 4],
                    ['Ventilator neonatus', 'Alat medis', 2], ['Syringe pump', 'Alat medis', $n(8)]],
                'Kamar Bersalin' => [['Bed partus', 'Alat medis', 4], ['CTG', 'Alat medis', 3], ['Doppler fetal', 'Alat medis', 3], ['Infant warmer', 'Alat medis', 2]],
                'Radiologi' => array_merge([['X-ray digital', 'Alat medis', 1], ['CT scan', 'Alat medis', 1], ['USG', 'Alat medis', $k ? 3 : 2], ['Mobile X-ray', 'Alat medis', 1], ['Panoramic dental', 'Alat medis', 1]],
                    $k ? [['Mammografi', 'Alat medis', 1]] : []),
                'Laboratorium' => [['Hematology analyzer', 'Alat medis', 2], ['Chemistry analyzer', 'Alat medis', 2], ['Centrifuge', 'Alat medis', 3], ['Biosafety cabinet', 'Alat medis', 1], ['Kulkas reagen', 'Alat medis', 3]],
                'Farmasi' => [['Kulkas obat (cold chain 2–8°C)', 'Alat medis', 4], ['Lemari narkotika', 'Furniture pasien', 2], ['Data logger suhu', 'Alat medis', 8, 'pcs']],
                'CSSD' => [['Autoclave', 'Alat medis', 2], ['Washer disinfector', 'Alat medis', 1], ['Plasma sterilizer', 'Alat medis', 1], ['Heat sealer', 'Alat medis', 2]],
                'Hemodialisa' => [['Mesin hemodialisa', 'Alat medis', $k ? 12 : 10], ['RO water treatment', 'HVAC & utilitas', 1], ['Kursi hemodialisa', 'Furniture pasien', $k ? 12 : 10]],
                'Rehabilitasi Medik' => $k
                    ? [['Robotic gait trainer', 'Alat medis', 1], ['Robotic upper limb trainer', 'Alat medis', 1], ['Treadmill terapi', 'Alat medis', 2], ['Shortwave diathermy', 'Alat medis', 2], ['Ultrasound terapi', 'Alat medis', 2]]
                    : [['Treadmill terapi', 'Alat medis', 1], ['Shortwave diathermy', 'Alat medis', 2], ['Ultrasound terapi', 'Alat medis', 1], ['TENS', 'Alat medis', 3]],
                'Poliklinik Rawat Jalan' => [['Bed periksa', 'Furniture pasien', $n(18)], ['Dental unit', 'Alat medis', 2], ['Timbangan bayi', 'Alat medis', 6], ['Tensimeter', 'Alat medis', 20, 'pcs'], ['Komputer poli', 'IT & SIMRS', $n(24)]],
                'Gizi' => [['Kompor', 'Dapur gizi', 6], ['Steamer', 'Dapur gizi', 2], ['Chiller dapur', 'Dapur gizi', 2], ['Freezer', 'Dapur gizi', 2], ['Troli makanan pemanas', 'Dapur gizi', 6]],
                'Laundry' => [['Mesin cuci', 'Laundry', 3], ['Dryer', 'Laundry', 2], ['Flatwork ironer', 'Laundry', 1]],
                'Utilitas & Gas Medis' => [['Genset', 'HVAC & utilitas', 2], ['Trafo', 'HVAC & utilitas', 1], ['Panel LVMDP', 'HVAC & utilitas', 1], ['UPS', 'HVAC & utilitas', 2], ['Chiller', 'HVAC & utilitas', 2],
                    ['Pompa air bersih', 'HVAC & utilitas', 3], ['Pompa hydrant', 'Keselamatan', 2], ['IPAL', 'HVAC & utilitas', 1], ['Tangki oksigen cair', 'Nurse call & gas medis', 1], ['Manifold gas medis', 'Nurse call & gas medis', 1],
                    ['Kompresor udara medis', 'Nurse call & gas medis', 2], ['Pompa vakum medis', 'Nurse call & gas medis', 2], ['Lift pasien', 'HVAC & utilitas', $k ? 4 : 3]],
                'Server & SIMRS' => [['Server SIMRS', 'IT & SIMRS', 2], ['Core switch', 'IT & SIMRS', 1], ['UPS server', 'IT & SIMRS', 1], ['CCTV', 'IT & SIMRS', $k ? 64 : 52, 'titik']],
                $k ? 'Aula Kemayoran' : 'Aula Bekasi' => [['Proyektor', 'Elektronik', 1], ['Sound system', 'Elektronik', 1], ['Kursi aula', 'Furniture pasien', $k ? 120 : 80, 'pcs']],
            ];
            foreach ($areas as $area => $items) {
                foreach ($items as $it) {
                    $rows[] = [$rs, $area, $it[0], $it[1], $it[3] ?? 'unit', $it[2]];
                }
            }
        }

        return $rows;
    }

    // ------------------------------------------------------------------ progres & riwayat (PROYEKSI)

    protected function surveyPlan(): array
    {
        return [
            [self::KMY, 4, -12, 1.0, ['sv1', 'sv2']],
            [self::BKS, 2, -11, 1.0, ['sv3', 'sv4']],
            [self::KMY, 5, -9, 1.0, ['sv1', 'sv2']],
            [self::BKS, 3, -8, 1.0, ['sv3', 'sv4']],
            [self::KMY, 3, -6, 1.0, ['sv1', 'sv2']],
            [self::BKS, 4, -5, 1.0, ['sv3', 'sv4']],
            [self::KMY, 6, -3, 0.65, ['sv1', 'sv2']],
            [self::BKS, 5, -2, 0.5, ['sv3', 'sv4']],
        ];
    }

    protected function surveyAreas(): array
    {
        return [
            ['ICU', self::KMY, -10, 'sv1'], ['Hemodialisa', self::BKS, -9, 'sv3'], ['Instalasi Bedah Sentral', self::KMY, -9, 'sv2'],
            ['Utilitas & Gas Medis', self::BKS, -7, 'sv4'], ['Radiologi', self::KMY, -7, 'sv1'], ['Utilitas & Gas Medis', self::KMY, -6, 'sv2'],
            ['Perinatologi & NICU', self::KMY, -5, 'sv2'], ['Gizi', self::KMY, -4, 'sv1'], ['Farmasi', self::BKS, -4, 'sv3'], ['IGD', self::BKS, -3, 'sv4'],
        ];
    }

    protected function prices(): array
    {
        return [
            'Robotic gait' => 6500000000, 'Robotic upper' => 2800000000, 'CT scan' => 9500000000, 'Mammografi' => 3200000000, 'C-arm' => 2800000000, 'X-ray digital' => 1800000000,
            'Mobile X-ray' => 750000000, 'Panoramic' => 650000000, 'USG' => 650000000, 'Mesin anestesi' => 750000000, 'Ventilator neonatus' => 520000000, 'Ventilator transport' => 280000000,
            'Ventilator' => 450000000, 'Meja operasi' => 400000000, 'Lampu operasi' => 250000000, 'Electrosurgical' => 180000000, 'Blood gas' => 550000000, 'Central monitor' => 320000000,
            'Patient monitor' => 85000000, 'Defibrillator' => 120000000, 'Infusion pump' => 28000000, 'Syringe pump' => 25000000, 'Inkubator' => 180000000, 'Infant warmer' => 95000000,
            'CPAP' => 220000000, 'Phototherapy' => 75000000, 'CTG' => 120000000, 'EKG' => 85000000, 'Mesin hemodialisa' => 450000000, 'RO water' => 600000000, 'Autoclave' => 650000000,
            'Washer disinfector' => 480000000, 'Plasma sterilizer' => 1200000000, 'Hematology' => 380000000, 'Chemistry' => 750000000, 'Biosafety' => 160000000, 'Bed ICU' => 95000000,
            'Bed pasien elektrik' => 35000000, 'Brankar' => 18000000, 'Genset' => 1400000000, 'Trafo' => 650000000, 'Chiller' => 1500000000, 'AHU' => 380000000, 'Lift pasien' => 1100000000,
            'Tangki oksigen' => 900000000, 'Manifold' => 250000000, 'Kompresor udara medis' => 320000000, 'Pompa vakum' => 210000000, 'Server SIMRS' => 180000000, 'Kulkas obat' => 32000000,
            'Steamer' => 65000000, 'Mesin cuci' => 320000000, 'Dryer' => 250000000, 'Flatwork' => 420000000, 'AC split' => 6500000, 'Televisi' => 4800000, 'Flowmeter' => 2500000,
            'Suction regulator' => 4500000, 'Tensimeter digital' => 3500000, 'Exhaust fan HEPA' => 28000000, 'Water heater' => 3800000, 'Kulkas kamar' => 3000000,
        ];
    }

    protected function cases(): array
    {
        $K = self::KMY.' > ';
        $B = self::BKS.' > ';

        // [hari, pelapor, kamar/area, aset, kategori, gejala, dampak, skenario, biaya[], diagnosis, tindakan, blokir?, estimasi?, catatan?] — ASUMSI
        return [
            [-27, 'hk', 'K402', 'AC split', 'ac', 'AC kamar rawat tidak dingin, pasien mengeluh panas', 'kamar_tidak_dijual', 'closed', [['material', 'Freon R32 + las pipa', 1, 650000]], 'Pipa evaporator bocor halus', 'Las pipa, vakum, isi ulang freon', 'block'],
            [-26, 'fbm', $K.'ICU', 'Ventilator', 'alat_medis', 'Ventilator 2 alarm tekanan tinggi berulang', 'keselamatan', 'closed', [['material', 'Flow sensor ventilator', 1, 2750000]], 'Flow sensor kotor / rusak', 'Ganti flow sensor, kalibrasi, uji fungsi 30 menit'],
            [-24, 'hk', 'K405', 'Bed pasien elektrik', 'furniture', 'Bed tidak bisa menaikkan sandaran kepala', 'layanan_alternatif', 'closed', [['material', 'Aktuator motor bed', 1, 1850000]], 'Aktuator motor sandaran rusak', 'Ganti aktuator, uji naik-turun & rem'],
            [-23, 'fbm', $K.'Instalasi Bedah Sentral', 'Mesin anestesi', 'alat_medis', 'Mesin anestesi OK 2 gagal leak test pagi', 'keselamatan', 'closed', [['vendor', 'Servis & kalibrasi oleh vendor resmi', 1, 4500000]], 'Seal canister absorber bocor', 'Ganti seal, leak test & kalibrasi ulang'],
            [-22, 'hk', 'B203', 'Flowmeter O2', 'alat_medis', 'Flowmeter O2 bed 3 tidak mengalirkan oksigen', 'keselamatan', 'closed', [['material', 'Flowmeter O2 pengganti', 1, 1250000]], 'Katup flowmeter macet', 'Ganti flowmeter, uji kebocoran outlet'],
            [-20, 'fo', 'B305', 'Televisi', 'elektronik', 'TV kamar tidak ada siaran', 'kosmetik', 'closed', [], 'Kabel antena longgar', 'Kencangkan konektor, scan ulang saluran'],
            [-19, 'fbm', $B.'Hemodialisa', 'Mesin hemodialisa', 'alat_medis', 'Mesin HD 4 alarm konduktivitas saat tindakan', 'keselamatan', 'closed', [['vendor', 'Kalibrasi sensor konduktivitas', 1, 1750000]], 'Sensor konduktivitas bergeser (drift)', 'Kalibrasi ulang, uji dengan larutan standar'],
            [-18, 'hk', 'K410', 'Shower set', 'air', 'Aliran air shower kamar mandi kecil', 'layanan_alternatif', 'on_hold', [], null, null, null, 0, 'Menunggu cartridge shower dari distributor'],
            [-16, 'fbm', $K.'Gizi', 'Steamer', 'kitchen', 'Steamer gizi tidak panas, distribusi makan pasien terlambat', 'layanan_alternatif', 'in_progress', [['material', 'Elemen pemanas (pesan)', 1, 1650000]], null, null],
            [-15, 'hk', 'K503', 'Bedside cabinet', 'furniture', 'Laci bedside cabinet lepas', 'kosmetik', 'closed', [], 'Rel laci lepas', 'Pasang ulang rel laci'],
            [-13, 'fo', 'B302', 'AC split', 'ac', 'AC bocor air ke lantai, risiko pasien terpeleset', 'kamar_tidak_dijual', 'completed', [['material', 'Pompa drain + selang', 1, 520000], ['labor', 'Lembur teknisi', 3, 40000]], 'Saluran drain tersumbat, pompa drain rusak', 'Bersihkan saluran, ganti pompa drain', 'block'],
            [-12, 'ce', $B.'Utilitas & Gas Medis', 'Genset', 'listrik', 'Uji beban bulanan: genset 2 gagal start otomatis', 'keselamatan', 'in_progress', [['vendor', 'Pemeriksaan ATS oleh vendor', 1, 3500000]], null, null],
            [-10, 'hk', 'K507', 'Water heater', 'air', 'Water heater tidak panas', 'layanan_alternatif', 'assigned', [], null, null],
            [-10, 'fo', 'K507', 'Water heater', 'air', 'Air panas kamar K507 tidak keluar (laporan admisi)', 'layanan_alternatif', 'duplicate', [], null, null],
            [-9, 'ce', $K.'ICU', 'Ventilator', 'alat_medis', 'Ventilator 4: blower perlu overhaul (rekomendasi vendor), unit dicadangkan', 'layanan_alternatif', 'awaiting_approval', [], null, null, null, 21500000],
            [-8, 'hk', 'B206', 'Bed pasien elektrik', 'furniture', 'Remote bed pasien rusak', 'kosmetik', 'closed', [['material', 'Hand control bed', 1, 950000]], 'Kabel remote putus', 'Ganti hand control'],
            [-7, 'fo', $B.'Farmasi', 'Kulkas obat', 'alat_medis', 'Suhu kulkas vaksin naik ke 10°C (alarm data logger)', 'keselamatan', 'verified', [], 'Kondensor kotor, karet pintu kurang rapat', 'Bersihkan kondensor, setel pintu, pantau suhu 24 jam'],
            [-6, 'hk', 'K408', 'Kursi penunggu', 'furniture', 'Kaki kursi penunggu patah', 'kosmetik', 'closed', [['material', 'Lem + sekrup', 1, 75000]], 'Sambungan kaki lepas', 'Perkuat sambungan'],
            [-5, 'hk', 'B310', 'Wastafel', 'air', 'Wastafel mampet', 'layanan_alternatif', 'closed', [], 'Sifon tersumbat', 'Bersihkan sifon'],
            [-4, 'fbm', $K.'Perinatologi & NICU', 'Inkubator', 'alat_medis', 'Inkubator 3 suhu tidak stabil', 'keselamatan', 'triaged', [], null, null],
            [-3, 'hk', 'B208', 'Televisi', 'elektronik', 'Remote TV hilang', 'kosmetik', 'rejected', [], null, null, null, 0, 'Bukan kerusakan: remote dimintakan ke bagian rumah tangga'],
            [-2, 'fo', 'K605', 'AC split', 'ac', 'AC kamar tidak bisa diatur suhunya', 'kamar_tidak_dijual', 'approved', [], null, null],
            [-1, 'hk', 'B404', 'Tensimeter digital', 'alat_medis', 'Tensimeter dinding hasil tidak akurat', 'layanan_alternatif', 'submitted', [], null, null],
            [0, 'fo', $B.'IGD', 'Defibrillator', 'alat_medis', 'Defibrillator IGD gagal self-test pagi', 'keselamatan', 'submitted', [], null, null],
            [0, 'hk', 'K309', 'Exhaust fan HEPA', 'ac', 'Tekanan negatif ruang isolasi tidak tercapai (indikator merah)', 'keselamatan', 'submitted', [], null, null],
        ];
    }

    protected function pmPlan(): array
    {
        return [
            [self::KMY.' > Utilitas & Gas Medis', 'Genset', 'PM bulanan genset: uji beban 30 menit', 'Uji beban 30 menit, cek oli & air radiator'],
            [self::KMY.' > ICU', 'Defibrillator', 'Kalibrasi berkala defibrillator (uji energi)', 'Uji energi 50/100/200 J dalam toleransi, label kalibrasi diperbarui'],
        ];
    }

    protected function transferPlan(): array
    {
        return [
            [self::BKS.' > IGD', 'Infusion pump', 0, 'Pinjam pakai antar RS: infusion pump IGD Bekasi ke Gudang IPSRS Kemayoran (lonjakan pasien ICU)', true],
            ['K408', 'Kursi penunggu', -1, 'Kursi rusak ditarik ke gudang IPSRS untuk diperbaiki (usulan)', false],
        ];
    }
}
