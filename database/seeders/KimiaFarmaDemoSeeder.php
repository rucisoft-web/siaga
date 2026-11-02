<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\RoomType;

/**
 * DATA DEMO "Kimia Farma" — jaringan apotek, klinik pratama & laboratorium klinik satu area (Bandung Raya).
 *
 * Fakta dari Laporan Tahunan 2025 PT Kimia Farma Tbk: jaringan nasional 1.050 apotek, 386 klinik kesehatan,
 * 65 laboratorium medis, 45 distributor; layanan kefarmasian, klinik & diagnostik.
 * PROYEKSI / ASUMSI: jumlah & nama outlet di area Bandung Raya (diberi tanda "contoh"), tipe outlet, isi standar
 * per tipe, gudang distribusi area, hasil pendataan, laporan kerusakan, work order, biaya, mutasi.
 * Di aplikasi: "kamar" = OUTLET, "tipe kamar" = tipe outlet, "lantai" = rayon, "kamar tidak dapat dijual" = outlet / layanan tutup.
 */
class KimiaFarmaDemoSeeder extends ProjectedDemoSeeder
{
    public const CODE = 'KF';

    public const PASSWORD = 'KimiaFarma-2026!demo';

    public const KOTA = 'Wilayah Bandung Kota';

    public const TIMUR = 'Wilayah Bandung Timur & Selatan';

    public const BARAT = 'Wilayah Cimahi & Bandung Barat';

    public const AREA = 'Kantor & Gudang Area Bandung';

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
            'title' => 'Kimia Farma', 'sector' => 'Apotek, klinik & laboratorium · Bandung Raya',
            'summary' => '47 outlet di 3 wilayah: apotek 24 jam, apotek reguler, apotek + klinik pratama, laboratorium klinik, serta gudang distribusi area. Cold chain obat, POS kasir, alat klinik & lab, genset outlet.',
            'source' => 'Laporan Tahunan 2025 Kimia Farma (jaringan nasional 1.050 apotek, 386 klinik, 65 lab). Outlet area Bandung, isi, kondisi & riwayat perbaikan = proyeksi.',
        ];
    }

    protected function propertyInfo(): array
    {
        return ['business_type' => 'retail', 'name' => 'Kimia Farma – Area Bandung Raya (demo)', 'city' => 'Bandung Raya', 'star_rating' => null, 'room_count' => count($this->roomList()), 'primary_color' => '#0B5394', 'accent_color' => '#F28C28'];
    }

    protected function departments(): ?array
    {
        return ['EXE' => 'Manajemen Area (Business Manager)', 'ENG' => 'Teknik & Pemeliharaan', 'HK' => 'Operasional Apotek', 'FO' => 'Pelayanan Outlet', 'FB' => 'Klinik & Laboratorium',
            'ACC' => 'Keuangan & Akuntansi', 'LOG' => 'Logistik & Gudang', 'IT' => 'IT'];
    }

    protected function vendors(): array
    {
        return [
            ['V-COLD', 'CV Dingin Medika (asumsi)', 'Kulkas obat, cold room, mobil box berpendingin', '24 jam / 6 jam respons'],
            ['V-HVAC', 'CV Sejuk Teknik (asumsi)', 'AC split outlet', 'Jam kerja, respons 1 hari'],
            ['V-ALKES', 'PT Alkes Servis Prima (asumsi)', 'Servis & kalibrasi alat klinik & lab', 'Kontrak tahunan'],
            ['V-POS', 'PT Solusi Kasir Nusantara (asumsi)', 'POS, printer struk, jaringan outlet', 'Respons 4 jam'],
            ['V-GEN', 'PT Daya Genset Servis (asumsi)', 'Genset gudang & outlet 24 jam', 'Kontrak tahunan'],
        ];
    }

    protected function people(): array
    {
        return [
            'gm' => ['Business Manager Area Bandung (contoh)', 'Business Manager', 'EXE', ['gm']],
            'ce' => ['Supervisor Teknik & Pemeliharaan (contoh)', 'Supervisor Teknik', 'ENG', ['chief-engineering']],
            'ac' => ['Admin Aset Area (contoh)', 'Admin Aset', 'ENG', ['asset-controller']],
            'tek1' => ['Teknisi Refrigerasi & AC (contoh)', 'Teknisi', 'ENG', ['technician']],
            'tek2' => ['Teknisi Listrik & IT (contoh)', 'Teknisi', 'ENG', ['technician']],
            'tek3' => ['Teknisi Alkes & Umum (contoh)', 'Teknisi', 'ENG', ['technician']],
            'fin' => ['Manajer Keuangan Area (contoh)', 'Manajer Keuangan', 'ACC', ['finance']],
            'qc' => ['Internal Audit (contoh)', 'Internal Auditor', 'ACC', ['auditor']],
            'sv1' => ['Petugas Pendataan 1 (contoh)', 'Petugas pendataan', 'ENG', ['surveyor']],
            'sv2' => ['Petugas Pendataan 2 (contoh)', 'Petugas pendataan', 'HK', ['surveyor']],
            'sv3' => ['Petugas Pendataan 3 (contoh)', 'Petugas pendataan', 'ENG', ['surveyor']],
            'sv4' => ['Petugas Pendataan 4 (contoh)', 'Petugas pendataan', 'HK', ['surveyor']],
        ];
    }

    protected function peopleExtra(): array
    {
        return [
            'fo' => ['Apoteker Penanggung Jawab (contoh)', 'Apoteker Penanggung Jawab', 'FO', ['reporter', 'front-office']],
            'fom' => ['Manajer Operasional Apotek (contoh)', 'Manajer Operasional', 'HK', ['hod', 'front-office']],
            'hk' => ['Asisten Apoteker (contoh)', 'Tenaga Teknis Kefarmasian', 'HK', ['reporter']],
            'fbm' => ['Kepala Klinik & Lab (contoh)', 'Kepala Klinik', 'FB', ['hod']],
        ];
    }

    protected function custody(bool $technical): array
    {
        return $technical ? ['ENG', 'Teknik & Pemeliharaan'] : ['HK', 'Operasional Apotek'];
    }

    protected function warehouseName(): string
    {
        return 'Gudang Aset Area Bandung';
    }

    protected function blockReason(): string
    {
        return 'Perbaikan, outlet tutup sementara / pelayanan dialihkan ke outlet terdekat';
    }

    protected function blankStock(): int
    {
        return 80;
    }

    protected function engCategoryNames(): array
    {
        return ['Pendingin & cold chain', 'HVAC & listrik', 'IT & kasir', 'Alat klinik', 'Laboratorium', 'Keselamatan'];
    }

    protected function brands(): array
    {
        return ['Kulkas obat (cold chain 2–8°C)' => ['Haier Biomedical', 'HYC-260'], 'AC split' => ['Daikin', 'FTKC35'], 'Komputer kasir (POS)' => ['Lenovo', 'ThinkCentre M70'],
            'Printer struk' => ['Epson', 'TM-T82X'], 'Barcode scanner' => ['Zebra', 'DS2208'], 'UPS' => ['APC', 'BX1100']];
    }

    protected function extras(): array
    {
        return [['Kulkas display minuman', 'Pendingin & cold chain'], ['Rak promosi tambahan', 'Perabot apotek'], ['Kursi roda pinjaman', 'Alat klinik']];
    }

    protected function techMap(): array
    {
        return ['ac' => 'tek1', 'alat_medis' => 'tek3', 'listrik' => 'tek2', 'it' => 'tek2', 'furniture' => 'tek3', 'keselamatan' => 'tek3', 'lainnya' => 'tek1',
            'air' => 'tek3', 'elektronik' => 'tek2', 'kitchen' => 'tek1', 'mice' => 'tek2', 'bangunan' => 'tek3', 'lift' => 'tek2'];
    }

    // ------------------------------------------------------------------ struktur (PROYEKSI)

    protected function roomTypes(): array
    {
        return ['Apotek 24 Jam' => self::KOTA, 'Apotek Reguler' => self::KOTA, 'Apotek + Klinik Pratama' => self::KOTA, 'Laboratorium Klinik' => self::KOTA];
    }

    protected function roomItems(): array
    {
        //                                                                   24J Reg +Kl Lab
        return [
            ['Pendingin & cold chain', 'Kulkas obat (cold chain 2–8°C)', 'unit', [2, 1, 2, 1]],
            ['Pendingin & cold chain', 'Data logger suhu', 'unit', [3, 2, 3, 2]],
            ['Pendingin & cold chain', 'Termohigrometer ruang', 'unit', [1, 1, 1, 1]],
            ['HVAC & listrik', 'AC split', 'unit', [3, 2, 4, 3]],
            ['HVAC & listrik', 'UPS', 'unit', [2, 1, 2, 2]],
            ['HVAC & listrik', 'Genset portable', 'unit', [1, 0, 1, 1]],
            ['HVAC & listrik', 'Panel listrik outlet', 'unit', [1, 1, 1, 1]],
            ['IT & kasir', 'Komputer kasir (POS)', 'unit', [3, 2, 3, 2]],
            ['IT & kasir', 'Printer struk', 'unit', [3, 2, 3, 1]],
            ['IT & kasir', 'Barcode scanner', 'unit', [3, 2, 3, 1]],
            ['IT & kasir', 'Router & modem', 'unit', [1, 1, 1, 1]],
            ['IT & kasir', 'DVR CCTV', 'unit', [1, 1, 1, 1]],
            ['IT & kasir', 'Kamera CCTV', 'titik', [8, 4, 8, 6]],
            ['Perabot apotek', 'Rak obat', 'set', [14, 10, 12, 0]],
            ['Perabot apotek', 'Etalase kaca', 'unit', [4, 3, 3, 0]],
            ['Perabot apotek', 'Lemari narkotika & psikotropika', 'unit', [1, 1, 1, 0]],
            ['Perabot apotek', 'Meja racik', 'unit', [1, 1, 1, 0]],
            ['Perabot apotek', 'Kursi tunggu pelanggan', 'set', [3, 2, 4, 3]],
            ['Alat klinik', 'Tensimeter digital', 'unit', [1, 1, 2, 1]],
            ['Alat klinik', 'Timbangan badan digital', 'unit', [1, 1, 1, 0]],
            ['Alat klinik', 'Alat cek gula & kolesterol', 'unit', [1, 1, 1, 0]],
            ['Alat klinik', 'Bed periksa', 'unit', [0, 0, 2, 1]],
            ['Alat klinik', 'Dental unit', 'unit', [0, 0, 1, 0]],
            ['Alat klinik', 'Autoclave tabletop', 'unit', [0, 0, 1, 1]],
            ['Alat klinik', 'EKG 12 lead', 'unit', [0, 0, 1, 1]],
            ['Alat klinik', 'Nebulizer', 'unit', [0, 0, 1, 0]],
            ['Alat klinik', 'Lampu periksa', 'unit', [0, 0, 2, 0]],
            ['Alat klinik', 'Kulkas vaksin', 'unit', [0, 0, 1, 0]],
            ['Laboratorium', 'Hematology analyzer', 'unit', [0, 0, 0, 1]],
            ['Laboratorium', 'Chemistry analyzer', 'unit', [0, 0, 0, 1]],
            ['Laboratorium', 'Centrifuge', 'unit', [0, 0, 0, 2]],
            ['Laboratorium', 'Mikroskop binokuler', 'unit', [0, 0, 0, 2]],
            ['Laboratorium', 'Biosafety cabinet', 'unit', [0, 0, 0, 1]],
            ['Laboratorium', 'Kursi phlebotomy', 'unit', [0, 0, 0, 2]],
            ['Laboratorium', 'Kulkas reagen', 'unit', [0, 0, 0, 2]],
            ['Keselamatan', 'APAR', 'unit', [2, 1, 2, 2]],
            ['Keselamatan', 'Smoke detector', 'unit', [2, 1, 2, 2]],
            ['Keselamatan', 'Neon box / signage', 'unit', [2, 1, 2, 1]],
        ];
    }

    protected function layout(): array
    {
        return [
            self::KOTA => ['prefix' => 'BK', 'floors' => [
                1 => ['Apotek 24 Jam' => 3, 'Apotek Reguler' => 6, 'Apotek + Klinik Pratama' => 3],
                2 => ['Apotek Reguler' => 7, 'Apotek + Klinik Pratama' => 2, 'Laboratorium Klinik' => 1],
            ]],
            self::TIMUR => ['prefix' => 'BT', 'floors' => [
                1 => ['Apotek 24 Jam' => 1, 'Apotek Reguler' => 6, 'Apotek + Klinik Pratama' => 2],
                2 => ['Apotek Reguler' => 5, 'Apotek + Klinik Pratama' => 1, 'Laboratorium Klinik' => 1],
            ]],
            self::BARAT => ['prefix' => 'CB', 'floors' => [
                1 => ['Apotek 24 Jam' => 1, 'Apotek Reguler' => 5, 'Apotek + Klinik Pratama' => 2, 'Laboratorium Klinik' => 1],
            ]],
        ];
    }

    /** nama lokasi outlet (contoh) per wilayah, urut nomor outlet */
    protected function outletNames(): array
    {
        return [
            'BK' => ['Braga', 'Dago', 'Pasteur', 'Riau', 'Setiabudi', 'Cihampelas', 'Sukajadi', 'Lengkong', 'Asia Afrika', 'Buah Batu', 'Gatot Subroto', 'Pajajaran',
                'Supratman', 'Kiaracondong', 'Ahmad Yani', 'Lodaya', 'Burangrang', 'Turangga', 'Cicendo', 'Sukaluyu', 'Merdeka', 'Sudirman'],
            'BT' => ['Antapani', 'Arcamanik', 'Ujungberung', 'Cicaheum', 'Soekarno-Hatta', 'Margahayu', 'Bojongsoang', 'Dayeuhkolot', 'Kopo', 'Cibiru', 'Rancaekek', 'Banjaran',
                'Baleendah', 'Majalaya', 'Ciparay', 'Soreang'],
            'CB' => ['Cimahi Tengah', 'Cimahi Utara', 'Leuwigajah', 'Padalarang', 'Lembang', 'Cihanjuang', 'Ngamprah', 'Batujajar', 'Baros'],
        ];
    }

    /** "kamar" hasil impor → nama outlet; "lantai" → rayon */
    protected function afterImport(): void
    {
        $names = $this->outletNames();
        $label = ['Apotek 24 Jam' => 'Apotek Kimia Farma %s 24 Jam', 'Apotek Reguler' => 'Apotek Kimia Farma %s', 'Apotek + Klinik Pratama' => 'Apotek & Klinik Kimia Farma %s', 'Laboratorium Klinik' => 'Laboratorium Klinik Kimia Farma %s'];
        $seen = [];
        foreach ($this->roomList() as $r) {
            $pre = substr($r['no'], 0, 2);
            $place = $names[$pre][$seen[$pre] = ($seen[$pre] ?? -1) + 1] ?? $r['no'];
            Location::where('room_ref', $r['no'])->get()->each->update(['name' => sprintf($label[$r['type']], $place).' (contoh)']);
        }
        $rayon = [self::KOTA => [1 => 'Rayon Bandung Utara & Tengah', 2 => 'Rayon Bandung Selatan & Timur Kota'], self::TIMUR => [1 => 'Rayon Timur', 2 => 'Rayon Selatan (Kab. Bandung)'], self::BARAT => [1 => 'Rayon Cimahi & KBB']];
        foreach ($rayon as $bld => $fls) {
            $b = Location::where('name', $bld)->whereNull('parent_id')->first();
            foreach ($fls as $fl => $name) {
                Location::where('parent_id', $b?->id)->where('location_type', 'floor')->where('floor_label', (string) $fl)->get()->each->update(['name' => $name]);
            }
        }
        foreach (['Apotek 24 Jam' => 'Outlet 24 jam + genset', 'Apotek Reguler' => 'Outlet reguler', 'Apotek + Klinik Pratama' => 'Apotek + 2 ruang praktik', 'Laboratorium Klinik' => 'Lab + sampling'] as $n => $cfg) {
            RoomType::where('name', $n)->update(['bed_config' => $cfg]);
        }
    }

    protected function venues(): array
    {
        return [['Ruang Pelatihan Apoteker', self::AREA]];
    }

    protected function areaItems(): array
    {
        $A = self::AREA;

        return [
            [$A, 'Gudang Distribusi Area', 'Cold room 2–8°C', 'Pendingin & cold chain', 'unit', 2],
            [$A, 'Gudang Distribusi Area', 'Forklift elektrik', 'Gudang & armada', 'unit', 2],
            [$A, 'Gudang Distribusi Area', 'Hand pallet', 'Gudang & armada', 'unit', 6],
            [$A, 'Gudang Distribusi Area', 'Rak pallet', 'Gudang & armada', 'set', 40],
            [$A, 'Gudang Distribusi Area', 'Data logger suhu gudang', 'Pendingin & cold chain', 'pcs', 20],
            [$A, 'Gudang Distribusi Area', 'Genset', 'HVAC & listrik', 'unit', 1],
            [$A, 'Gudang Distribusi Area', 'Mobil box berpendingin', 'Gudang & armada', 'unit', 3],
            [$A, 'Gudang Distribusi Area', 'Motor pengantar obat', 'Gudang & armada', 'unit', 8],
            [$A, 'Kantor Area', 'Server', 'IT & kasir', 'unit', 1],
            [$A, 'Kantor Area', 'Core switch', 'IT & kasir', 'unit', 1],
            [$A, 'Kantor Area', 'UPS server', 'IT & kasir', 'unit', 1],
            [$A, 'Kantor Area', 'PC / laptop staf', 'IT & kasir', 'unit', 25],
            [$A, 'Kantor Area', 'Printer', 'IT & kasir', 'unit', 4],
            [$A, 'Kantor Area', 'AC split kantor', 'HVAC & listrik', 'unit', 10],
            [$A, 'Ruang Pelatihan Apoteker', 'Proyektor', 'IT & kasir', 'unit', 1],
            [$A, 'Ruang Pelatihan Apoteker', 'Sound system', 'IT & kasir', 'unit', 1],
            [$A, 'Ruang Pelatihan Apoteker', 'Kursi pelatihan', 'Perabot apotek', 'pcs', 40],
        ];
    }

    // ------------------------------------------------------------------ progres & riwayat (PROYEKSI)

    protected function surveyPlan(): array
    {
        return [
            [self::KOTA, 1, -12, 1.0, ['sv1', 'sv2']],
            [self::BARAT, 1, -10, 1.0, ['sv3', 'sv4']],
            [self::KOTA, 2, -7, 1.0, ['sv1', 'sv2']],
            [self::TIMUR, 1, -4, 0.6, ['sv3', 'sv4']],
            [self::TIMUR, 2, -1, 0.3, ['sv1', 'sv2']],
        ];
    }

    protected function surveyAreas(): array
    {
        return [['Gudang Distribusi Area', self::AREA, -6, 'sv1'], ['Kantor Area', self::AREA, -4, 'sv2'], ['Ruang Pelatihan Apoteker', self::AREA, -2, 'sv3']];
    }

    protected function prices(): array
    {
        return [
            'Kulkas obat' => 18000000, 'Kulkas vaksin' => 25000000, 'Kulkas reagen' => 15000000, 'Data logger suhu' => 1500000, 'Termohigrometer' => 450000, 'AC split' => 6000000,
            'UPS server' => 18000000, 'UPS' => 4500000, 'Genset portable' => 12000000, 'Genset' => 450000000, 'Panel listrik' => 8500000, 'Komputer kasir' => 9500000, 'Printer struk' => 2800000,
            'Barcode' => 1600000, 'Router' => 1800000, 'DVR' => 3500000, 'Etalase' => 4500000, 'Lemari narkotika' => 3500000, 'Meja racik' => 3000000, 'Tensimeter' => 1200000,
            'Timbangan' => 900000, 'Alat cek gula' => 750000, 'Bed periksa' => 4500000, 'Dental unit' => 95000000, 'Autoclave' => 28000000, 'EKG' => 35000000, 'Nebulizer' => 1500000,
            'Lampu periksa' => 2500000, 'Hematology' => 280000000, 'Chemistry' => 450000000, 'Centrifuge' => 18000000, 'Mikroskop' => 25000000, 'Biosafety' => 120000000,
            'Kursi phlebotomy' => 6500000, 'Cold room' => 350000000, 'Forklift' => 380000000, 'Mobil box' => 520000000, 'Motor pengantar' => 22000000, 'Server' => 85000000,
            'Core switch' => 45000000, 'Proyektor' => 12000000, 'Sound system' => 18000000, 'Neon box' => 7500000, 'APAR' => 650000,
        ];
    }

    protected function cases(): array
    {
        $G = self::AREA.' > Gudang Distribusi Area';

        // [hari, pelapor, outlet/area, aset, kategori, gejala, dampak, skenario, biaya[], diagnosis, tindakan, blokir?, estimasi?, catatan?] — ASUMSI
        return [
            [-27, 'fo', 'BK101', 'Kulkas obat', 'alat_medis', 'Suhu kulkas obat 11°C, alarm data logger semalam (stok insulin & vaksin dipindah)', 'keselamatan', 'closed', [['material', 'Relay & kapasitor kompresor', 1, 450000]], 'Relay start kompresor rusak', 'Ganti relay & kapasitor, pantau suhu 24 jam, laporan ekskursi suhu ke apoteker'],
            [-25, 'fo', 'BK104', 'Komputer kasir', 'it', 'POS kasir 2 tidak bisa transaksi non-tunai', 'layanan_alternatif', 'closed', [], 'Pembaruan aplikasi gagal', 'Pasang ulang aplikasi POS, sinkron ulang data'],
            [-24, 'fo', 'BK110', 'AC split', 'ac', 'AC ruang tunggu klinik tidak dingin', 'layanan_alternatif', 'closed', [['material', 'Freon R32', 1, 450000]], 'Freon kurang', 'Isi freon, cek kebocoran'],
            [-22, 'fo', 'BK102', 'Genset portable', 'listrik', 'Genset apotek 24 jam tidak menyala saat PLN padam', 'keselamatan', 'closed', [['material', 'Aki genset 12V', 1, 850000]], 'Aki soak', 'Ganti aki, uji start & perpindahan beban'],
            [-20, 'fbm', 'CB107', 'Autoclave tabletop', 'alat_medis', 'Autoclave klinik gagal mencapai 121°C', 'keselamatan', 'closed', [['vendor', 'Servis & validasi autoclave', 1, 1750000]], 'Elemen pemanas lemah', 'Ganti elemen, uji Bowie-Dick & indikator biologis'],
            [-19, 'fbm', 'BK210', 'Chemistry analyzer', 'alat_medis', 'Hasil QC kimia klinik di luar rentang', 'layanan_alternatif', 'closed', [['vendor', 'Kalibrasi & ganti lampu fotometer', 1, 3200000]], 'Lampu halogen fotometer melemah', 'Ganti lampu, kalibrasi, QC 2 level lolos'],
            [-18, 'fo', 'BK106', 'Etalase kaca', 'furniture', 'Kaca etalase depan retak', 'kosmetik', 'on_hold', [], null, null, null, 0, 'Menunggu kaca tempered ukuran khusus dari supplier'],
            [-16, 'ce', $G, 'Cold room', 'ac', 'Cold room 1 suhu naik ke 9°C, stok dipindah ke cold room 2', 'keselamatan', 'in_progress', [['material', 'Kipas evaporator (pesan)', 1, 2350000]], null, null],
            [-15, 'hk', 'CB103', 'Printer struk', 'it', 'Printer struk macet', 'kosmetik', 'closed', [], 'Kertas tersangkut, head kotor', 'Bersihkan head & jalur kertas'],
            [-13, 'fo', 'BK108', 'Panel listrik outlet', 'listrik', 'MCB utama sering trip, apotek gelap sebagian', 'kamar_tidak_dijual', 'completed', [['material', 'MCB & kabel NYM', 1, 780000], ['labor', 'Lembur teknisi', 3, 40000]], 'Beban AC menumpuk di satu fasa', 'Seimbangkan beban fasa, ganti MCB', 'block'],
            [-12, 'ce', $G, 'Genset', 'listrik', 'Uji beban bulanan: genset gudang gagal start otomatis', 'keselamatan', 'in_progress', [['vendor', 'Pemeriksaan ATS oleh vendor', 1, 3500000]], null, null],
            [-10, 'fo', 'BK205', 'Kulkas obat', 'alat_medis', 'Kulkas obat berembun & berbunga es', 'layanan_alternatif', 'assigned', [], null, null],
            [-10, 'hk', 'BK205', 'Kulkas obat', 'alat_medis', 'Kulkas obat BK205 berembun (laporan shift 2)', 'layanan_alternatif', 'duplicate', [], null, null],
            [-9, 'ce', $G, 'Mobil box berpendingin', 'lainnya', 'Unit pendingin mobil box 2 tidak mencapai suhu, perlu overhaul kompresor', 'layanan_alternatif', 'awaiting_approval', [], null, null, null, 14500000],
            [-8, 'fbm', 'CB108', 'EKG', 'alat_medis', 'Rekaman EKG klinik bergaris (noise)', 'layanan_alternatif', 'closed', [['material', 'Kabel pasien EKG', 1, 1150000]], 'Kabel pasien rusak', 'Ganti kabel, uji dengan simulator'],
            [-7, 'fbm', 'BK111', 'Dental unit', 'alat_medis', 'Handpiece dental unit tidak berputar', 'layanan_alternatif', 'verified', [], 'Selang udara bocor', 'Ganti selang & o-ring'],
            [-6, 'hk', 'BK203', 'Meja racik', 'furniture', 'Meja racik goyang', 'kosmetik', 'closed', [['material', 'Baut & bracket', 1, 85000]], 'Baut kaki longgar', 'Kencangkan & tambah bracket'],
            [-5, 'fo', 'CB105', 'Router', 'it', 'Internet outlet putus, transaksi non-tunai gagal', 'layanan_alternatif', 'closed', [], 'Modem hang', 'Restart modem, atur ulang jadwal reboot otomatis'],
            [-4, 'hk', 'BT102', 'Barcode scanner', 'it', 'Scanner barcode tidak membaca kemasan', 'kosmetik', 'triaged', [], null, null],
            [-3, 'fo', 'BK207', 'Neon box', 'listrik', 'Neon box minta diganti desain baru', 'kosmetik', 'rejected', [], null, null, null, 0, 'Bukan kerusakan: diteruskan ke tim marketing (permintaan desain)'],
            [-2, 'fo', 'BT104', 'AC split', 'ac', 'AC apotek mati total, suhu ruang obat > 30°C', 'kamar_tidak_dijual', 'approved', [], null, null],
            [-1, 'hk', 'BT105', 'Data logger suhu', 'alat_medis', 'Data logger tidak mengirim data suhu', 'layanan_alternatif', 'submitted', [], null, null],
            [0, 'fbm', 'CB109', 'Hematology analyzer', 'alat_medis', 'Hematology analyzer error aspirasi sampel', 'layanan_alternatif', 'submitted', [], null, null],
            [0, 'fo', 'BK103', 'Lemari narkotika', 'keselamatan', 'Kunci ganda lemari narkotika macet', 'keselamatan', 'submitted', [], null, null],
        ];
    }

    protected function pmPlan(): array
    {
        return [
            [self::AREA.' > Gudang Distribusi Area', 'Genset', 'PM bulanan genset gudang: uji beban 30 menit', 'Uji beban 30 menit, cek oli & air radiator'],
            ['BK101', 'Kulkas obat', 'Pemetaan suhu & kalibrasi data logger kulkas obat (triwulan)', 'Pemetaan suhu 24 jam, data logger dikalibrasi, hasil dalam 2–8°C'],
        ];
    }

    protected function transferPlan(): array
    {
        return [
            ['BK205', 'Data logger suhu', -6, 'Data logger cadangan ditarik ke gudang area untuk dikalibrasi', true],
            ['CB104', 'Komputer kasir', -1, 'POS lama ditarik ke gudang area untuk diperiksa (usulan)', false],
        ];
    }
}
