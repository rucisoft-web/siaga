<?php

namespace Database\Seeders;

use App\Models\ApprovalLimit;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetFinancial;
use App\Models\AssetLabel;
use App\Models\AssetQuantityGroup;
use App\Models\AssetTransfer;
use App\Models\Department;
use App\Models\Location;
use App\Models\Property;
use App\Models\Role;
use App\Models\RoomAssetTemplate;
use App\Models\RoomBlock;
use App\Models\RoomSurvey;
use App\Models\ServiceRequest;
use App\Models\SlaPolicy;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Services\AssetService;
use App\Services\LabelService;
use App\Services\MasterImportService;
use App\Services\RoomTemplateService;
use App\Services\ServiceRequestService;
use App\Services\SurveyService;
use App\Services\TransferService;
use App\Services\WorkOrderService;
use App\Support\Branding;
use App\Support\CurrentProperty;
use App\Support\GuestAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * DATA DEMO GPCH untuk situs pra-workshop & pelatihan. Properti terpisah (kode GPCH) dari "Hotel Contoh".
 *
 * Sumber: workbook master aset GPCH (database/demo/gpch-master-aset.xlsx): tipe & jumlah kamar, isi kamar per tipe,
 * ruang meeting, area umum & estimasi. Yang bersifat PROYEKSI / ASUMSI (ditandai "contoh"/"asumsi"):
 *  - nomor kamar & lantai (workbook belum memuat nomor kamar),
 *  - hasil pendataan (kondisi, selisih, aset tambahan), jumlah hitung fisik,
 *  - laporan kerusakan, work order, biaya, mutasi, blokir kamar, nilai perolehan,
 *  - akun jabatan, vendor, batas persetujuan.
 */
class GpchDemoSeeder extends Seeder
{
    public const CODE = 'GPCH';

    public const PASSWORD = 'Gpch-2026!demo';

    /** tanggal acuan "hari ini" data demo: semua kejadian disusun mundur dari sini */
    protected Carbon $today;

    protected Property $p;

    protected array $u = [];

    protected array $log = [];

    public function run(): void
    {
        mt_srand(2026);
        Carbon::setTestNow();
        $this->today = Carbon::now(config('assetcare.display_timezone', 'Asia/Jakarta'))->startOfDay()->utc();
        $this->p = $this->property();
        app(CurrentProperty::class)->set($this->p);
        Branding::flush();
        $this->reference();
        $this->users();

        $this->as('ac');
        $this->at(-30, 9);
        $this->setupData();
        $this->areasExtra();
        $this->generate();
        $this->printLabels();
        $this->survey();
        $this->areaSurvey();
        $this->activate();
        $this->financials();
        $this->history();
        $this->maintenance();
        $this->transfers();
        $this->controls();
        Carbon::setTestNow();
        $this->groupDirector();
        GuestAccess::ensure($this->p);
        Auth::logout();
    }

    /** pesan kegagalan, dikelompokkan: "jenis: pesan (×n)" */
    public function messages(): array
    {
        return collect($this->log)->countBy()->map(fn ($n, $m) => $m.($n > 1 ? ' (×'.$n.')' : ''))->values()->all();
    }

    // ------------------------------------------------------------------ titik kustomisasi (data demo lain menurunkan kelas ini)

    public function code(): string
    {
        return self::CODE;
    }

    public function mail(string $local): string
    {
        return $local.'@'.strtolower($this->code()).'.demo';
    }

    /** Contoh direksi grup: peran global (property_id null) agar bisa melihat dasbor grup dan berpindah perusahaan. */
    public function groupDirector(): User
    {
        $u = User::withoutGlobalScopes()->firstOrCreate(['email' => 'direksi@grup.demo'], [
            'name' => 'Direksi Grup (demo)', 'job_title' => 'Direktur Operasional Grup (contoh)', 'password' => $this->password(),
            'property_id' => $this->p->id, 'is_active' => true, 'must_change_password' => true,
        ]);
        $role = Role::where('slug', 'group-director')->value('id');
        if ($role && ! $u->roles()->where('roles.id', $role)->wherePivotNull('property_id')->exists()) {
            $u->roles()->attach($role, ['property_id' => null]);
        }

        return $u;
    }

    public function password(): string
    {
        return self::PASSWORD;
    }

    /** nama perusahaan, ringkasan & sumber data untuk halaman pilih data demo */
    public static function about(): array
    {
        return ['title' => 'Grand Pasundan Convention Hotel', 'sector' => 'Hotel & MICE · Bandung', 'summary' => '164 kamar di dua gedung, 10 ruang meeting, kitchen, laundry & power house. Pendataan berjalan minggu ke-3, laporan kerusakan & work order 30 hari, riwayat biaya 6 bulan.', 'source' => 'Workbook master aset GPCH; nomor kamar, hasil pendataan & riwayat perbaikan = proyeksi.'];
    }

    protected function propertyInfo(): array
    {
        return ['name' => 'Grand Pasundan Convention Hotel (demo)', 'city' => 'Bandung', 'star_rating' => 4, 'room_count' => 164, 'primary_color' => '#1F3A5F', 'accent_color' => '#B8860B'];
    }

    /** null = salin departemen & kategori dari properti pertama; array = [kode => nama] departemen sendiri (kategori dari workbook) */
    protected function departments(): ?array
    {
        return null;
    }

    protected function vendors(): array
    {
        return [
            ['V-HVAC', 'CV Sejuk Teknik (asumsi)', 'AC & chiller', '24 jam / 4 jam respons'],
            ['V-GEN', 'PT Daya Genset Servis (asumsi)', 'Genset & panel listrik', 'Kontrak tahunan, respons 6 jam'],
            ['V-KIT', 'CV Dapur Prima (asumsi)', 'Peralatan kitchen', 'Jam kerja'],
            ['V-ELV', 'PT Lift Andalan (asumsi)', 'Lift & eskalator', 'Kontrak bulanan'],
        ];
    }

    protected function people(): array
    {
        return [
            'gm' => ['General Manager (contoh)', 'General Manager', 'EXE', ['gm']],
            'ce' => ['Chief Engineer (contoh)', 'Chief Engineer', 'ENG', ['chief-engineering']],
            'ac' => ['Asset Controller (contoh)', 'Engineering Admin / Asset Controller', 'ENG', ['asset-controller']],
            'tek1' => ['Teknisi HVAC (contoh)', 'Teknisi Engineering', 'ENG', ['technician']],
            'tek2' => ['Teknisi Listrik (contoh)', 'Teknisi Engineering', 'ENG', ['technician']],
            'tek3' => ['Teknisi Plumbing (contoh)', 'Teknisi Engineering', 'ENG', ['technician']],
            'fin' => ['Chief Accountant (contoh)', 'Chief Accountant', 'ACC', ['finance']],
            'qc' => ['Internal Audit / QC (contoh)', 'Internal Auditor', 'ACC', ['auditor']],
            'sv1' => ['Petugas Pendataan 1 (contoh)', 'Petugas pendataan', 'ENG', ['surveyor']],
            'sv2' => ['Petugas Pendataan 2 (contoh)', 'Petugas pendataan', 'HK', ['surveyor']],
            'sv3' => ['Petugas Pendataan 3 (contoh)', 'Petugas pendataan', 'ENG', ['surveyor']],
            'sv4' => ['Petugas Pendataan 4 (contoh)', 'Petugas pendataan', 'HK', ['surveyor']],
        ];
    }

    /** [kode departemen, unit penanggung jawab] untuk aset teknis / aset ruang saat aktivasi */
    protected function custody(bool $technical): array
    {
        return $technical ? ['ENG', 'Engineering'] : ['HK', 'Housekeeping'];
    }

    protected function warehouseName(): string
    {
        return 'Gudang Engineering';
    }

    /** estimasi area bersatuan "unit" sampai jumlah ini didata per unit (label QR), di atasnya dihitung */
    protected function unitThreshold(): int
    {
        return 6;
    }

    protected function blankStock(): int
    {
        return 54;
    }

    protected function blockReason(): string
    {
        return 'Perbaikan, kamar tidak layak jual sementara';
    }

    /** struktur: tipe ruang, template isi, lokasi & ruang */
    protected function setupData(): void
    {
        app(MasterImportService::class)->import(database_path('demo/gpch-master-aset.xlsx'), $this->u['ce'], false, true);
        $this->rooms();
    }

    protected function brands(): array
    {
        return ['Televisi' => ['LG', '43LM5750'], 'AC indoor' => ['Daikin', 'FTKC25'], 'Mini refrigerator' => ['Electrolux', 'EUM0500'], 'Brankas kamar' => ['Elsafe', 'Infinity'], 'Electric kettle' => ['Philips', 'HD9306'], 'Hair dryer' => ['Panasonic', 'EH-ND30']];
    }

    protected function extras(): array
    {
        return [['Kursi lounge tambahan', 'FURN'], ['Standing lamp', 'LAMP'], ['Meja rias portable', 'FURN']];
    }

    protected function engCategoryNames(): array
    {
        return ['Elektronik', 'Mekanikal', 'Elektrikal', 'Keamanan', 'Kamar mandi'];
    }

    protected function peopleExtra(): array
    {
        return [
            'fo' => ['FO Supervisor (contoh)', 'Front Office Supervisor', 'FO', ['reporter', 'front-office']],
            'fom' => ['Front Office Manager (contoh)', 'Front Office Manager', 'FO', ['hod', 'front-office']],
            'hk' => ['HK Supervisor (contoh)', 'Housekeeping Supervisor', 'HK', ['reporter']],
            'fbm' => ['Banquet Manager (contoh)', 'Banquet Manager', 'FB', ['hod']],
        ];
    }

    protected function surveyAreas(): array
    {
        return [
            ['Power house', 'Sentral / Bersama', -6, 'sv1'], ['Main Kitchen', 'Main Building', -5, 'sv3'],
            ['Plant & semua gedung', 'Main Building', -4, 'sv1'], ['Server room & jaringan', 'Sentral / Bersama', -4, 'sv3'],
            ['Laundry', 'Sentral / Bersama', -2, 'sv2'], ['Sangkuriang', null, -1, 'sv4'], ['Jayagiri', null, -1, 'sv4'],
        ];
    }

    protected function techMap(): array
    {
        return ['ac' => 'tek1', 'listrik' => 'tek2', 'air' => 'tek3', 'elektronik' => 'tek2', 'furniture' => 'tek3', 'kitchen' => 'tek1', 'keselamatan' => 'tek2', 'it' => 'tek2', 'mice' => 'tek2', 'bangunan' => 'tek3', 'lainnya' => 'tek2', 'lift' => 'tek2'];
    }

    /** gudang tujuan mutasi & rencana mutasi [kode ruang, nama aset, hari, alasan, selesai?] */
    protected function transferPlan(): array
    {
        return [['215', 'Hair dryer', -7, 'Unit rusak ditarik ke gudang setelah diganti (WO hair dryer 215)', true], ['312', 'Televisi', -1, 'TV dipindah ke gudang untuk diperiksa (usulan)', false]];
    }

    protected function pmPlan(): array
    {
        return [['Power house', 'Genset utama', 'PM bulanan genset: uji beban 30 menit', 'Uji beban 30 menit, cek oli & air radiator']];
    }

    protected function prices(): array
    {
        return ['Genset' => 850000000, 'Trafo' => 450000000, 'Panel' => 120000000, 'Chiller' => 1200000000, 'Cooling' => 350000000, 'Pompa' => 45000000, 'Air handling' => 180000000,
            'UPS' => 95000000, 'Server' => 120000000, 'Core switch' => 60000000, 'Mesin cuci' => 280000000, 'Dryer' => 220000000, 'Ironer' => 350000000,
            'Combi oven' => 185000000, 'Oven' => 85000000, 'Chiller kitchen' => 65000000, 'Freezer' => 55000000, 'Proyektor' => 25000000, 'Sound' => 45000000,
            'Televisi' => 4800000, 'AC indoor' => 5500000, 'Mini refrigerator' => 2300000, 'Brankas' => 2900000, 'Kunci pintu' => 4200000];
    }

    // ------------------------------------------------------------------ dasar

    protected function property(): Property
    {
        $info = $this->propertyInfo();
        $p = Property::firstOrCreate(['code' => $this->code()], ['name' => $info['name'], 'timezone' => 'Asia/Jakarta']);
        $p->forceFill($info + [
            'legal_name' => 'Data demo pelatihan — bukan data resmi', 'app_name' => 'SIAGA', 'tagline' => 'Sistem Informasi Aset & Gangguan',
            'currency' => 'IDR', 'timezone' => 'Asia/Jakarta', 'is_active' => true,
        ])->save();

        return $p;
    }

    protected function reference(): void
    {
        $src = Property::orderBy('id')->first();
        $own = $this->departments();
        foreach ($own ?? Department::withoutGlobalScopes()->where('property_id', $src->id)->pluck('name', 'code')->all() as $code => $name) {
            Department::withoutGlobalScopes()->firstOrCreate(['property_id' => $this->p->id, 'code' => $code], ['name' => $name, 'is_active' => true]);
        }
        foreach ($own === null ? AssetCategory::withoutGlobalScopes()->where('property_id', $src->id)->get() : [] as $c) {
            AssetCategory::withoutGlobalScopes()->firstOrCreate(['property_id' => $this->p->id, 'code' => $c->code], collect($c->getAttributes())->except(['id', 'property_id', 'code', 'created_at', 'updated_at'])->all());
        }
        foreach (SlaPolicy::withoutGlobalScopes()->where('property_id', $src->id)->get() as $s) {
            SlaPolicy::withoutGlobalScopes()->firstOrCreate(['property_id' => $this->p->id, 'priority' => $s->priority], collect($s->getAttributes())->except(['id', 'property_id', 'priority', 'created_at', 'updated_at'])->all());
        }
        foreach ($this->vendors() as [$code, $name, $spec, $sla]) {
            Vendor::withoutGlobalScopes()->firstOrCreate(['property_id' => $this->p->id, 'code' => $code], ['name' => $name, 'specialization' => $spec, 'contract_sla' => $sla, 'is_active' => true]);
        }
    }

    public static function email(string $local): string
    {
        return $local.'@gpch.demo';
    }

    protected function users(): void
    {
        $dept = fn ($c) => Department::withoutGlobalScopes()->where('property_id', $this->p->id)->where('code', $c)->value('id');
        $people = $this->people() + $this->peopleExtra();
        $roles = Role::pluck('id', 'slug');
        foreach ($people as $k => [$name, $title, $d, $r]) {
            $u = User::withoutGlobalScopes()->firstOrCreate(['email' => $this->mail($k)], [
                'name' => $name, 'job_title' => $title, 'password' => $this->password(), 'property_id' => $this->p->id,
                'department_id' => $dept($d), 'is_active' => true, 'must_change_password' => true,
            ]);
            $u->roles()->syncWithoutDetaching(collect($r)->mapWithKeys(fn ($s) => [$roles[$s] => ['property_id' => $this->p->id]])->all());
            $this->u[$k] = $u->fresh();
        }
        User::whereIn('email', [$this->mail('tek1'), $this->mail('tek2'), $this->mail('tek3')])->update(['labor_rate' => 40000]);
        foreach ([['ce', 2500000], ['gm', 25000000], ['fin', 10000000]] as [$k, $max]) {
            ApprovalLimit::withoutGlobalScopes()->firstOrCreate(['property_id' => $this->p->id, 'user_id' => $this->u[$k]->id, 'object_type' => 'work_order'], [
                'max_amount' => $max, 'valid_from' => now()->startOfYear(), 'note' => 'CONTOH — nominal wajib disahkan manajemen',
            ]);
        }
    }

    // ------------------------------------------------------------------ waktu & pengguna

    /** Geser jam sistem ke H$day (negatif = lalu) pukul $hour:$min agar riwayat tampak alami. */
    protected function at(int $day, int $hour = 9, int $min = 0): void
    {
        Carbon::setTestNow();
        $real = Carbon::now();
        $t = $this->today->copy()->addDays($day)->setTime($hour, $min, mt_rand(0, 59));
        Carbon::setTestNow($t->greaterThan($real) ? $real->copy()->subMinutes(mt_rand(5, 90)) : $t);
    }

    protected function as(string $k): User
    {
        $u = User::find($this->u[$k]->id);
        Auth::login($u);

        return $u;
    }

    protected function try(string $what, callable $fn): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            $this->log[] = preg_replace('/ [A-Z]+-[A-Z]+-\d+| \d{3,}| W?\d{3}$/', '', $what).': '.$e->getMessage();

            return null;
        }
    }

    // ------------------------------------------------------------------ kamar (nomor = PROYEKSI)

    protected function rooms(): void
    {
        if (Location::where('location_type', 'room')->exists()) {
            return;
        }
        $plan = [
            'Main Building' => [
                'types' => ['Deluxe A King' => 22, 'Deluxe A Twin' => 39, 'Executive A King' => 18, 'Executive A Twin' => 6, 'Premiere Suite' => 4, 'Grand Family Suite' => 3, 'President Suite' => 1, '3 Bedroom Pasundan Suite' => 1],
                'floors' => [2 => 15, 3 => 15, 4 => 16, 5 => 15, 6 => 12, 7 => 12, 8 => 9], 'prefix' => '',
            ],
            'West Wing' => [
                'types' => ['Standard King' => 5, 'Standard Twin' => 10, 'Deluxe B King' => 22, 'Deluxe B Twin' => 24, 'Executive B King' => 6, 'Executive B Twin' => 3],
                'floors' => [1 => 15, 2 => 18, 3 => 19, 4 => 18], 'prefix' => 'W',
            ],
        ];
        $rows = [];
        foreach ($plan as $bld => $cfg) {
            $queue = [];
            foreach ($cfg['types'] as $t => $n) {
                array_push($queue, ...array_fill(0, $n, $t));
            }
            foreach ($cfg['floors'] as $fl => $cap) {
                for ($i = 1; $i <= $cap; $i++) {
                    $rows[] = ['nomor kamar' => $cfg['prefix'].($fl * 100 + $i), 'jenis kamar' => array_shift($queue), 'gedung' => $bld, 'lantai' => (string) $fl];
                }
            }
        }
        app(MasterImportService::class)->addRooms($rows);
    }

    /** Status PMS contoh agar tampilan kamar bervariasi. */
    protected function areasExtra(): void
    {
        $st = ['vacant', 'vacant', 'occupied', 'occupied', 'occupied', 'vacant'];
        Location::where('location_type', 'room')->orderBy('id')->get()->each(fn ($r, $i) => $r->forceFill(['pms_room_status' => $st[$i % 6]])->saveQuietly());
    }

    protected function generate(): void
    {
        $svc = app(RoomTemplateService::class);
        $ac = $this->as('ac');
        $day = -26;
        foreach (RoomAssetTemplate::where('status', 'published')->with('roomType')->get() as $tpl) {
            $rooms = Location::where('location_type', 'room')->where('room_type_id', $tpl->room_type_id)->orderBy('sort_key')->pluck('id')->all();
            if (! $rooms || $tpl->roomType === null) {
                continue;
            }
            $this->at($day, 10, mt_rand(0, 50));
            $this->try('generate '.$tpl->code, fn () => $svc->commit($svc->createBatch($tpl, $rooms, $svc->preview($tpl, $rooms), $ac, 'demo-gpch-'.$tpl->code), [], $ac));
        }
    }

    protected function roomsOn(string $building, int $floor): \Illuminate\Support\Collection
    {
        $b = Location::where('name', $building)->first();
        $f = Location::where('parent_id', $b?->id)->where('location_type', 'floor')->where('floor_label', (string) $floor)->first();

        return Location::where('parent_id', $f?->id)->where('location_type', 'room')->orderBy('sort_key')->get();
    }

    /** lantai yang sudah / sedang didata (PROYEKSI progres minggu ke-3 implementasi) */
    protected function surveyPlan(): array
    {
        return [
            // [gedung, lantai, hari mulai, porsi selesai (1 = semua), petugas]
            ['Main Building', 2, -12, 1.0, ['sv1', 'sv2']],
            ['West Wing', 1, -11, 1.0, ['sv3', 'sv4']],
            ['Main Building', 3, -8, 1.0, ['sv1', 'sv2']],
            ['West Wing', 2, -7, 1.0, ['sv3', 'sv4']],
            ['Main Building', 4, -3, 0.6, ['sv1', 'sv2']],
            ['West Wing', 3, -2, 0.35, ['sv3', 'sv4']],
        ];
    }

    protected function printLabels(): void
    {
        $ls = app(LabelService::class);
        $ac = $this->as('ac');
        foreach ($this->surveyPlan() as [$b, $fl, $day]) {
            $rooms = $this->roomsOn($b, $fl);
            $labels = AssetLabel::where('status', 'active')->whereHas('asset', fn ($q) => $q->whereIn('location_id', $rooms->pluck('id')))->get();
            if ($labels->isEmpty()) {
                continue;
            }
            $this->at($day - 2, 14);
            $batch = $this->try('cetak '.$b.$fl, fn () => $ls->createBatch($labels->sortBy('asset_id')->values(), 'a4-50x30', 1, $ac, ['lantai' => $b.' '.$fl]));
            if ($batch) {
                $ls->markDownloaded($batch);
                $ls->confirmPrinted($batch, $ac);
            }
        }
        $this->at(-14, 15);
        $this->try('stok label', fn () => $ls->issueBlankBatch($this->blankStock(), 'a4-50x30', 1, $ac));
    }

    // ------------------------------------------------------------------ pendataan (PROYEKSI hasil lapangan)

    protected function condition(): string
    {
        $r = mt_rand(1, 100);

        return $r <= 68 ? 'A' : ($r <= 88 ? 'B' : ($r <= 96 ? 'C' : ($r <= 99 ? 'D' : 'E')));
    }

    protected function survey(): void
    {
        $svc = app(SurveyService::class);
        $brands = $this->brands();
        $stock = AssetLabel::where('status', 'unassigned')->orderBy('id')->get();
        foreach ($this->surveyPlan() as [$b, $fl, $day, $portion, $team]) {
            $rooms = $this->roomsOn($b, $fl);
            $take = (int) ceil($rooms->count() * $portion);
            foreach ($rooms->values() as $i => $room) {
                if ($i >= $take + ($portion < 1 ? 1 : 0)) {
                    break;
                }
                $partial = $portion < 1 && $i === $take; // satu kamar sedang dikerjakan
                $who = $team[$i % 2];
                $u = $this->as($who);
                $d = $day + intdiv($i, 8);
                $this->at(min($d, 0), 8 + ($i % 8), mt_rand(0, 45));
                $items = $svc->items($room);
                foreach ($items as $k => $a) {
                    if ($partial && $k > $items->count() / 2) {
                        break;
                    }
                    Carbon::setTestNow(now()->addSeconds(mt_rand(15, 40)));
                    $missing = mt_rand(1, 220) === 1;
                    if (! $missing) {
                        $this->try('scan '.$a->asset_code, fn () => $svc->scan($room, $a->activeLabel->url(), $u));
                    }
                    $det = isset($brands[$a->name]) && mt_rand(1, 3) === 1 ? ['brand' => $brands[$a->name][0], 'model' => $brands[$a->name][1], 'serial_number' => 'SN-'.strtoupper(Str::random(8))] : [];
                    $this->try('catat '.$a->asset_code, fn () => $svc->record($a->fresh(), ! $missing, $missing ? null : $this->condition(), $det + ($missing ? ['note' => 'Tidak ditemukan saat pendataan (asumsi demo)'] : []), $u));
                }
                if ($partial) {
                    continue;
                }
                foreach ($svc->groups($room) as $g) {
                    $exp = (float) $g->expected_qty;
                    $qty = mt_rand(1, 8) === 1 ? max(0, $exp - mt_rand(1, 2)) : $exp;
                    $this->try('hitung', fn () => $svc->countGroup($g, $qty, $u));
                }
                if (mt_rand(1, 9) === 1 && $stock->isNotEmpty()) {
                    $extra = collect($this->extras())->random();
                    $cat = AssetCategory::where('code', $extra[1])->orWhere('name', $extra[1])->value('id') ?? AssetCategory::value('id');
                    $this->try('tambahan', fn () => $svc->addExtra($room, ['name' => $extra[0], 'category_id' => $cat, 'condition_grade' => $this->condition()], $u, $stock->shift()));
                }
                $this->try('selesai '.$room->room_ref, fn () => $svc->finish($room, $u));
            }
        }
    }

    /** Area teknis: aset utama didata per unit (dari estimasi master), sisanya dihitung. */
    protected function areaSurvey(): void
    {
        $svc = app(SurveyService::class);
        $stock = AssetLabel::where('status', 'unassigned')->orderBy('id')->get();
        $areas = $this->surveyAreas();
        foreach ($areas as [$name, $bld, $day, $who]) {
            $q = Location::whereIn('location_type', ['area', 'venue'])->where('name', $name);
            if ($bld) {
                $q->whereHas('parent', fn ($w) => $w->where('name', $bld));
            }
            $area = $q->first();
            if (! $area) {
                $this->log[] = 'area tidak ditemukan: '.$name;

                continue;
            }
            $u = $this->as($who);
            $this->at($day, 9, mt_rand(0, 30));
            foreach ($svc->groups($area) as $g) {
                Carbon::setTestNow(now()->addMinutes(mt_rand(2, 6)));
                $exp = (int) $g->expected_qty;
                // aset utama (jumlah kecil, satuan unit) → didata per unit dengan label stok kosong
                if ($g->unit === 'unit' && $exp >= 1 && $exp <= $this->unitThreshold() && $stock->count() >= $exp) {
                    for ($n = 1; $n <= $exp; $n++) {
                        $label = $stock->shift();
                        $this->try('aset area', fn () => $svc->addExtra($area, ['name' => $g->name.($exp > 1 ? ' '.$n : ''), 'category_id' => $g->category_id, 'condition_grade' => $this->condition(), 'brand' => null], $u, $label));
                    }
                    $g->forceFill(['status' => 'retired', 'on_hand' => $exp])->save(); // estimasi diganti aset per unit
                    continue;
                }
                $qty = mt_rand(1, 5) === 1 ? max(0, $exp + mt_rand(-3, 2)) : $exp;
                $this->try('hitung area', fn () => $svc->countGroup($g, $qty, $u));
            }
            $this->try('selesai '.$name, fn () => $svc->finish($area, $u));
        }
    }

    protected function activate(): void
    {
        $ce = $this->as('ac');
        $this->at(-1, 16);
        $dept = fn ($c) => Department::where('code', $c)->value('id');
        $done = RoomSurvey::where('status', 'done')->pluck('location_id');
        $engCats = AssetCategory::whereIn('name', $this->engCategoryNames())->pluck('id')->all();
        $assets = Asset::whereIn('location_id', $done)->where('verification_status', 'verified')->where('lifecycle_status', 'draft')->get();
        $svc = app(AssetService::class);
        foreach ($assets as $a) {
            $loc = $a->location;
            $eng = in_array($a->category_id, $engCats, true) || $loc?->location_type !== 'room';
            [$dc, $unit] = $this->custody($eng);
            $a->forceFill([
                'department_id' => $a->department_id ?: $dept($dc),
                'criticality' => $a->criticality ?: ($loc?->location_type === 'room' ? 'low' : 'high'),
                'custodian_unit' => $a->custodian_unit ?: $unit,
                'ownership' => $a->ownership ?: 'milik',
            ])->saveQuietly();
            $this->try('aktivasi', fn () => $svc->activate($a->fresh(), $ce));
        }
    }

    /** Stock opname (1 selesai + 1 berjalan) dan penghapusan aset di berbagai status. Dapat dipanggil ulang pada data demo lama. */
    public function controls(?Property $p = null): void
    {
        if ($p) {
            $this->p = $p;
            app(CurrentProperty::class)->set($p);
            $this->today = Carbon::now(config('assetcare.display_timezone', 'Asia/Jakarta'))->startOfDay()->utc();
            foreach (['ac', 'fin', 'gm', 'ce'] as $k) {
                $this->u[$k] = User::where('email', $this->mail($k))->firstOrFail();
            }
        }
        if (\App\Models\StockTake::exists()) {
            return;
        }
        mt_srand(77);
        $so = app(\App\Services\StockTakeService::class);
        $floors = Location::where('location_type', 'floor')->orderBy('sort_key')->get()
            ->map(fn ($f) => [$f, Asset::whereIn('location_id', $so->scopeIds($f->id))->whereNotIn('lifecycle_status', ['disposed', 'lost'])->count()])
            ->filter(fn ($x) => $x[1] >= 30)->values();
        if ($floors->count() < 1) {
            return;
        }
        // 1) sesi selesai
        $ac = $this->as('ac');
        $this->at(-21, 8);
        $take = $so->open(['title' => 'Stock opname semester II (contoh) · '.$floors[0][0]->full_path, 'location_id' => $floors[0][0]->id, 'cutoff_date' => $this->today->copy()->addDays(-21)->toDateString(), 'notes' => 'Data demo'], $ac);
        $items = $take->items()->with('asset')->get();
        $rooms = $items->pluck('expected_location_id')->filter()->unique()->values();
        $n = $items->count();
        foreach ($items as $i => $it) {
            $this->at(-21 + intdiv($i * 3, max(1, $n)), 9 + ($i % 8), $i % 60);
            if ($i % 41 === 7 && $rooms->count() > 1) {
                $to = $rooms->first(fn ($r) => $r !== $it->expected_location_id);
                $so->mark($take, $it, $to, null, null, $ac);
            } elseif ($i % 53 === 11) {
                $so->mark($take, $it, null, null, 'Dicari di gudang & ruang sebelah, tidak ada', $ac, true);
            } else {
                $grade = $i % 37 === 5 ? (($it->asset?->condition_grade ?? 'B') === 'A' ? 'C' : 'D') : ($it->asset?->condition_grade ? null : 'B');
                $so->mark($take, $it, null, $grade, null, $ac);
            }
        }
        $so->addUnregistered($take, 'Kursi lipat besi (4 unit) tanpa label', $rooms->first(), 'Kemungkinan pindahan dari ballroom', $ac);
        $so->addUnregistered($take, 'Dispenser air panas-dingin', $rooms->last(), 'Merek Miyako, kondisi baik', $ac);
        $this->at(-17, 16);
        $so->close($take, true, $ac);
        // tindak lanjut: aset tidak ditemukan diusulkan hapus (hilang)
        $ds = app(\App\Services\DisposalService::class);
        $lost = $take->items()->where('result', 'not_found')->with('asset')->get();
        foreach ($lost->take(2) as $k => $it) {
            $this->as('ac');
            $this->at(-15, 10 + $k);
            $d = $this->try('penghapusan', fn () => $ds->propose($it->asset, ['method' => 'lost', 'reason' => 'Tidak ditemukan pada '.$take->number, 'stock_take_id' => $take->id, 'justification' => 'Sudah dicari ulang 2x bersama Housekeeping & Security. (contoh)'], $this->u['ac']));
            if ($d && $k === 0) {
                $this->as('fin');
                $this->at(-12, 11);
                $ds->decide($d, true, 'Disetujui, catat sebagai kerugian aset hilang (contoh)', $this->u['fin']);
                $this->as('ac');
                $this->at(-10, 14);
                $ds->complete($d->fresh(), ['document_ref' => 'BA-HPS/'.$this->today->format('Y').'/001 (contoh)'], $this->u['ac']);
            }
        }
        // 2) sesi berjalan
        if ($floors->count() > 1) {
            $this->as('ac');
            $this->at(-2, 8);
            $t2 = $so->open(['title' => 'Stock opname tahunan (contoh) · '.$floors[1][0]->full_path, 'location_id' => $floors[1][0]->id, 'cutoff_date' => $this->today->copy()->addDays(-2)->toDateString()], $ac);
            $it2 = $t2->items()->orderBy('expected_location_id')->orderBy('id')->get();
            foreach ($it2->take((int) ($it2->count() * .42)) as $i => $it) {
                $this->at(-2 + intdiv($i, 60), 9 + ($i % 8), $i % 60);
                $i % 47 === 9 ? $so->mark($t2, $it, null, null, null, $ac, true) : $so->mark($t2, $it, null, null, null, $ac);
            }
        }
        // 3) penghapusan aset rusak berat / dijual
        $cands = Asset::with('financial')->whereIn('condition_grade', ['D', 'E'])->whereHas('financial', fn ($q) => $q->where('acquisition_cost', '>', 0))
            ->whereNotIn('lifecycle_status', ['disposed', 'lost'])->with('category')->get()
            ->sortByDesc(fn ($a) => (new \App\Services\DepreciationService)->forAsset($a, \Carbon\CarbonImmutable::now())['book'] ?? 0)->take(4)->values();
        $plan = [
            ['scrap', 'Rusak berat, biaya perbaikan > 60% harga baru', 'submitted'],
            ['sale', 'Diganti unit baru, unit lama dijual ke pengepul', 'approved'],
            ['scrap', 'Korosi berat, tidak aman dipakai', 'completed'],
            ['donation', 'Masih berfungsi, dihibahkan ke yayasan mitra', 'rejected'],
        ];
        foreach ($cands as $k => $a) {
            [$method, $reason, $end] = $plan[$k];
            $this->as($k % 2 ? 'ce' : 'ac');
            $this->at(-9 + $k * 2, 10);
            $requester = $k % 2 ? $this->u['ce'] : $this->u['ac'];
            $proceeds = $method === 'sale' ? round((float) $a->financial->acquisition_cost * .08, -3) : null;
            $d = $this->try('penghapusan', fn () => $ds->propose($a, ['method' => $method, 'reason' => $reason.' (contoh)', 'proceeds' => $proceeds, 'justification' => 'Kajian teknis Chief Engineer terlampir. (contoh)'], $requester));
            if (! $d || $end === 'submitted') {
                continue;
            }
            $this->as('gm');
            $this->at(-8 + $k * 2, 15);
            $ds->decide($d, $end !== 'rejected', $end === 'rejected' ? 'Masih layak pakai; pindahkan ke area back office' : 'Setuju', $this->u['gm']);
            if ($end === 'completed') {
                $this->as('ac');
                $this->at(-7 + $k * 2, 10);
                $ds->complete($d->fresh(), ['document_ref' => 'BA-HPS/'.$this->today->format('Y').'/00'.($k + 2).' (contoh)'], $this->u['ac']);
            }
        }
    }

    protected function financials(): void
    {
        $price = $this->prices();
        foreach (Asset::where('lifecycle_status', 'active')->get() as $a) {
            $base = collect($price)->first(fn ($v, $k) => Str::startsWith($a->name, $k));
            if (! $base) {
                continue;
            }
            AssetFinancial::firstOrCreate(['asset_id' => $a->id], [
                'acquisition_date' => now()->subYears(mt_rand(3, 12))->startOfYear()->addDays(mt_rand(0, 300))->toDateString(),
                'acquisition_cost' => round($base * mt_rand(85, 115) / 100, -3), 'replacement_estimate' => round($base * 1.35, -3),
                'cost_center' => 'ENG-OPS', 'notes' => 'ASUMSI demo — bukan nilai buku resmi', 'verification_status' => 'unverified',
            ]);
        }
    }

    // ------------------------------------------------------------------ riwayat 6 bulan (PROYEKSI, untuk tren dasbor pimpinan)

    /** pola kerusakan per kata kunci nama aset: [regex, kategori laporan, mode kegagalan[], biaya min, biaya maks, dampak] */
    protected function failurePatterns(): array
    {
        return [
            ['/^AC|AHU|Exhaust|Chiller$|Cooling|Air handling/i', 'ac', ['Freon bocor', 'Saluran drain tersumbat', 'Kapasitor kompresor rusak', 'PCB indoor rusak'], 250000, 2800000, 'kamar_tidak_dijual'],
            ['/Kulkas|Freezer|Chiller kitchen|Cold room|Mini refrigerator|Mobil box/i', 'ac', ['Thermostat rusak', 'Kompresor lemah', 'Karet pintu tidak rapat'], 300000, 4500000, 'keselamatan'],
            ['/monitor|Ventilator|pump|Defibrillator|Inkubator|warmer|analyzer|EKG|USG|X-ray|CT scan|Autoclave|hemodialisa|anestesi|Dental|CPAP|CTG|Suction|Flowmeter|Tensimeter|Phototherapy|Centrifuge|Nebulizer/i', 'alat_medis', ['Hasil ukur bergeser, perlu kalibrasi', 'Baterai internal lemah', 'Aksesori / kabel pasien rusak', 'Board kontrol error'], 400000, 12000000, 'keselamatan'],
            ['/Genset|Panel|UPS|Trafo|Lampu|lamp/i', 'listrik', ['Aki / baterai soak', 'MCB / kontaktor rusak', 'Sambungan kabel longgar'], 150000, 6500000, 'layanan_alternatif'],
            ['/Kloset|Wastafel|Shower|Pompa|Water heater|Sink/i', 'air', ['Seal / karet aus', 'Saluran tersumbat', 'Valve bocor'], 75000, 1500000, 'layanan_alternatif'],
            ['/Komputer|Computer|Printer|POS|Router|Server|scanner|CCTV|DVR|switch|Access point/i', 'it', ['Hang / perlu pasang ulang', 'Adaptor / power supply rusak', 'Kabel jaringan putus'], 0, 2500000, 'layanan_alternatif'],
            ['/Televisi|TV|Proyektor|Sound|Telepon/i', 'elektronik', ['Konektor longgar', 'Lampu / panel redup', 'Remote / input rusak'], 0, 2200000, 'kosmetik'],
            ['/Bed|Kursi|Meja|Lemari|cabinet|Sofa|Etalase|table/i', 'furniture', ['Sambungan lepas', 'Roda / rem rusak', 'Aktuator rusak'], 50000, 1900000, 'kosmetik'],
            ['/Lift/i', 'lift', ['Sensor pintu kotor', 'Rem / tali perlu penyetelan'], 500000, 9500000, 'keselamatan'],
            ['/Oven|Kompor|Steamer|Mesin cuci|Dryer|ironer/i', 'kitchen', ['Elemen pemanas putus', 'Thermostat rusak', 'Bearing aus'], 250000, 5500000, 'layanan_alternatif'],
        ];
    }

    /** WO selesai 6 bulan ke belakang (H-200 s.d. H-31) pada aset aktif: tren biaya, MTTR, SLA, kerusakan berulang, blokir. */
    protected function history(): void
    {
        $ce = $this->as('ce');
        $techs = [$this->u['tek1'], $this->u['tek2'], $this->u['tek3']];
        $patterns = $this->failurePatterns();
        $pool = [];
        foreach (Asset::where('lifecycle_status', 'active')->with('location')->get(['id', 'name', 'location_id']) as $a) {
            foreach ($patterns as $i => $pt) {
                if (preg_match($pt[0], $a->name)) {
                    $pool[] = [$a, $i];
                    break;
                }
            }
        }
        if (! $pool) {
            return;
        }
        // aset "langganan rusak": beberapa aset muncul berkali-kali → kerusakan berulang & biaya tertinggi
        $bad = array_map(fn () => $pool[mt_rand(0, count($pool) - 1)], range(1, 8));
        $hours = ['P1' => 4, 'P2' => 24, 'P3' => 72, 'P4' => 168];
        $n = (int) min(110, max(60, count($pool) / 12));
        for ($k = 0; $k < $n; $k++) {
            [$a, $pi] = mt_rand(1, 100) <= 28 ? $bad[mt_rand(0, 7)] : $pool[mt_rand(0, count($pool) - 1)];
            [, $cat, $modes, $min, $max, $impact] = $patterns[$pi];
            $mode = $modes[mt_rand(0, count($modes) - 1)];
            $prio = $impact === 'keselamatan' ? (mt_rand(0, 1) ? 'P1' : 'P2') : ($impact === 'kamar_tidak_dijual' ? 'P2' : (mt_rand(0, 3) ? 'P3' : 'P4'));
            // makin dekat makin banyak (adopsi pelaporan meningkat)
            $day = -31 - (int) floor(169 * (1 - sqrt(mt_rand(0, 1000) / 1000)));
            $this->at($day, mt_rand(7, 21), mt_rand(0, 59));
            $recv = now()->copy();
            $due = $recv->copy()->addHours($hours[$prio]);
            $late = mt_rand(1, 100) <= 18;
            $restored = $late ? $due->copy()->addHours(mt_rand(2, 48)) : $recv->copy()->addMinutes(mt_rand(30, (int) ($hours[$prio] * 60 * 0.9)));
            $cost = $max > 0 && mt_rand(1, 100) <= 70 ? round(mt_rand($min, $max), -3) : 0;
            $acq = (float) AssetFinancial::where('asset_id', $a->id)->value('acquisition_cost');
            if ($cost && $acq > 0) {
                $cost = min($cost, max(50000, round($acq * mt_rand(4, 40) / 100, -3))); // perbaikan wajar terhadap harga aset
            }
            $tech = $techs[mt_rand(0, 2)];
            $sr = $this->try('riwayat lapor', fn () => ServiceRequest::create([
                'asset_id' => $a->id, 'location_id' => $a->location_id, 'reporter_id' => ($this->u['hk'] ?? $ce)->id, 'category' => $cat,
                'symptom' => $a->name.': '.Str::lcfirst($mode).' (riwayat proyeksi)', 'impact' => $impact, 'is_safety' => $impact === 'keselamatan', 'priority' => $prio,
                'status' => 'closed', 'received_at' => $recv, 'response_due_at' => $recv->copy()->addMinutes($prio === 'P1' ? 15 : 60),
                'response_at' => $recv->copy()->addMinutes(mt_rand(5, $late ? 120 : 50)), 'responded_by' => $ce->id, 'triaged_at' => $recv->copy()->addMinutes(10), 'triaged_by' => $ce->id,
                'resolved_at' => $restored, 'closed_at' => $restored->copy()->addHours(4),
            ]));
            $wo = $this->try('riwayat WO', fn () => WorkOrder::create([
                'type' => 'corrective', 'title' => Str::limit($a->name.': '.$mode, 80), 'description' => 'Riwayat proyeksi (data demo) — rekap pemeliharaan sebelum SIAGA',
                'asset_id' => $a->id, 'location_id' => $a->location_id, 'priority' => $prio, 'is_safety' => $impact === 'keselamatan', 'status' => 'closed',
                'received_at' => $recv, 'response_due_at' => $recv->copy()->addHour(), 'due_at' => $due, 'estimated_cost' => $cost, 'approved_by' => $ce->id, 'approved_at' => $recv->copy()->addMinutes(20),
                'pic_id' => $tech->id, 'started_at' => $recv->copy()->addMinutes(40), 'failure_mode' => $mode, 'diagnosis' => $mode, 'root_cause' => $mode,
                'action_taken' => 'Perbaikan / penggantian komponen sesuai diagnosis', 'function_test_result' => 'pass', 'evidence_exception_reason' => 'Riwayat proyeksi',
                'restored_at' => $restored, 'completed_at' => $restored, 'completed_by' => $tech->id, 'cost_status' => 'final', 'no_cost_reason' => $cost ? null : 'Tanpa material',
                'verified_by' => $ce->id, 'verified_at' => $restored->copy()->addHours(2), 'asset_status_after' => 'serviceable', 'closed_at' => $restored->copy()->addHours(3), 'closed_by' => $ce->id,
                'created_by' => $ce->id, 'created_at' => $recv, 'updated_at' => $restored,
            ]));
            if (! $wo) {
                continue;
            }
            if ($sr) {
                $wo->requests()->attach($sr->id, ['created_at' => $recv, 'updated_at' => $recv]);
            }
            if ($cost > 0) {
                $type = $cost > 2500000 ? 'vendor' : 'material';
                \App\Models\WoCostLine::create(['work_order_id' => $wo->id, 'type' => $type, 'description' => $type === 'vendor' ? 'Jasa servis vendor (riwayat)' : 'Suku cadang (riwayat)',
                    'quantity' => 1, 'unit_cost' => $cost, 'amount' => $cost, 'cost_status' => 'final', 'is_cash_out' => true, 'created_by' => $ce->id, 'finalized_by' => $this->u['fin']->id,
                    'finalized_at' => $restored->copy()->addDay(), 'period' => $restored->format('Y-m'), 'created_at' => $restored, 'updated_at' => $restored]);
                if (mt_rand(0, 1)) {
                    $lab = mt_rand(1, 4);
                    \App\Models\WoCostLine::create(['work_order_id' => $wo->id, 'type' => 'labor', 'description' => 'Jam kerja teknisi', 'quantity' => $lab, 'unit_cost' => 40000, 'amount' => $lab * 40000,
                        'cost_status' => 'final', 'is_cash_out' => false, 'created_by' => $ce->id, 'finalized_by' => $this->u['fin']->id, 'finalized_at' => $restored->copy()->addDay(),
                        'period' => $restored->format('Y-m'), 'created_at' => $restored, 'updated_at' => $restored]);
                }
            }
            if ($impact === 'kamar_tidak_dijual' && $a->location?->isRoom()) {
                RoomBlock::create(['room_id' => $a->location_id, 'work_order_id' => $wo->id, 'status' => 'released', 'started_at' => $recv->copy()->addMinutes(30), 'ended_at' => $restored,
                    'reason' => $this->blockReason().' (riwayat)', 'proposed_by' => $ce->id, 'fo_confirmed_by' => ($this->u['fom'] ?? $ce)->id, 'fo_confirmed_at' => $recv->copy()->addMinutes(30)]);
            }
        }
    }

    // ------------------------------------------------------------------ pemeliharaan (PROYEKSI 30 hari)

    protected function asset(string $room, string $name): ?Asset
    {
        $loc = Location::where('room_ref', $room)->orWhere(fn ($q) => $q->whereIn('location_type', ['area', 'venue'])->where('name', $room))->pluck('id');

        return Asset::whereIn('location_id', $loc)->where('name', 'like', $name.'%')->orderBy('id')->first();
    }

    /** [hari, pelapor, ruang, nama aset, kategori, gejala, dampak, akhir, biaya[], diagnosis, tindakan, 'block'?, estimasi?] — ASUMSI */
    protected function cases(): array
    {
        // [hari, pelapor, kamar/area, nama aset, kategori, gejala, dampak, skenario, biaya[], diagnosis, tindakan]
        return [
            [-27, 'hk', '203', 'AC indoor', 'ac', 'AC tidak dingin, air menetes dari unit indoor', 'kamar_tidak_dijual', 'closed', [['material', 'Freon R32 + las pipa', 1, 650000]], 'Pipa evaporator bocor halus', 'Las pipa, vakum, isi freon R32', 'block'],
            [-25, 'fo', '305', 'Televisi', 'elektronik', 'TV tidak menampilkan saluran, hanya layar biru', 'layanan_alternatif', 'closed', [], 'Kabel antena longgar di wall plate', 'Kencangkan konektor, scan ulang saluran'],
            [-24, 'hk', '212', 'Kloset', 'air', 'Kloset terus mengalir setelah disiram', 'layanan_alternatif', 'closed', [['material', 'Flush valve set', 1, 285000]], 'Karet flush valve aus', 'Ganti flush valve set'],
            [-22, 'fo', '309', 'Kunci pintu elektronik', 'keselamatan', 'Kartu tamu tidak bisa membuka pintu', 'kamar_tidak_dijual', 'closed', [['material', 'Baterai AA alkaline', 4, 12500]], 'Baterai lemah', 'Ganti baterai, uji 5 kartu'],
            [-20, 'hk', '214', 'Smoke detector', 'keselamatan', 'Smoke detector berbunyi beep berkala', 'keselamatan', 'closed', [['material', 'Baterai 9V', 1, 45000]], 'Baterai cadangan habis', 'Ganti baterai, uji fungsi detektor'],
            [-19, 'fo', 'Sangkuriang', 'Proyektor', 'mice', 'Proyektor mati mendadak saat acara pagi', 'acara_terganggu', 'closed', [['vendor', 'Servis lampu proyektor (vendor)', 1, 1850000]], 'Lampu proyektor habis masa pakai', 'Ganti lampu, reset jam lampu'],
            [-18, 'hk', 'W105', 'Set shower', 'air', 'Tekanan air shower kecil', 'layanan_alternatif', 'on_hold', [], null, null],
            [-16, 'fbm', 'Main Kitchen', 'Combi oven', 'kitchen', 'Combi oven tidak mencapai suhu, error E34', 'acara_terganggu', 'in_progress', [['material', 'Sensor suhu (pesan)', 1, 1450000]], null, null],
            [-15, 'hk', '208', 'Brankas kamar', 'keselamatan', 'Brankas terkunci, tamu lupa PIN', 'layanan_alternatif', 'closed', [], 'Pengguna lupa PIN', 'Buka dengan master code, reset PIN'],
            [-13, 'fo', 'W210', 'AC indoor', 'ac', 'AC berbunyi keras dan bocor air ke lantai', 'kamar_tidak_dijual', 'completed', [['material', 'Pompa drain + selang', 1, 520000], ['labor', 'Lembur teknisi', 3, 40000]], 'Selang drain tersumbat, pompa drain rusak', 'Bersihkan saluran, ganti pompa drain', 'block'],
            [-12, 'ce', 'Power house', 'Genset utama', 'listrik', 'Uji beban bulanan: genset 1 gagal start otomatis', 'keselamatan', 'in_progress', [['vendor', 'Pemeriksaan ATS oleh vendor', 1, 3500000]], null, null],
            [-10, 'hk', '401', 'Mini refrigerator', 'elektronik', 'Kulkas mini tidak dingin', 'layanan_alternatif', 'assigned', [], null, null],
            [-10, 'fo', '401', 'Mini refrigerator', 'elektronik', 'Minibar 401 tidak dingin (laporan FO)', 'layanan_alternatif', 'duplicate', [], null, null],
            [-9, 'ce', 'Plant & semua gedung', 'Chiller', 'ac', 'Chiller 1 berisik, arus kompresor naik', 'layanan_alternatif', 'awaiting_approval', [], null, null, null, 12500000],
            [-8, 'hk', '215', 'Hair dryer', 'elektronik', 'Hair dryer mati total', 'kosmetik', 'closed', [['material', 'Hair dryer pengganti', 1, 385000]], 'Motor terbakar', 'Ganti unit baru (aset lama diusulkan dihapus)'],
            [-7, 'fo', 'Main & West Lobby', 'Computer front office', 'it', 'PC front desk 2 sangat lambat', 'layanan_alternatif', 'verified', [], 'Disk penuh, update tertunda', 'Bersihkan disk, pasang update'],
            [-6, 'hk', '304', 'Kursi kerja', 'furniture', 'Kaki kursi kerja patah', 'kosmetik', 'closed', [['material', 'Lem kayu + sekrup', 1, 75000]], 'Sambungan kaki lepas', 'Lem & perkuat dengan sekrup'],
            [-5, 'hk', '206', 'Wastafel', 'air', 'Wastafel mampet', 'layanan_alternatif', 'closed', [], 'Rambut menyumbat sifon', 'Bersihkan sifon'],
            [-4, 'fbm', 'Jayagiri', 'Sound system', 'mice', 'Mic wireless putus-putus', 'acara_terganggu', 'triaged', [], null, null],
            [-3, 'hk', 'W112', 'Televisi', 'elektronik', 'Remote TV hilang', 'kosmetik', 'rejected', [], null, null],
            [-2, 'fo', '309', 'AC indoor', 'ac', 'AC kamar tidak bisa diatur suhunya', 'kamar_tidak_dijual', 'approved', [], null, null],
            [-1, 'hk', '311', 'Lampu baca/tidur', 'listrik', 'Lampu baca kiri tidak menyala', 'kosmetik', 'submitted', [], null, null],
            [0, 'fo', 'Main Kitchen', 'Freezer', 'kitchen', 'Alarm suhu freezer berbunyi', 'acara_terganggu', 'submitted', [], null, null],
            [0, 'hk', 'W204', 'Kunci pintu elektronik', 'keselamatan', 'Pintu kamar tidak terkunci rapat', 'keselamatan', 'submitted', [], null, null],
        ];
    }

    protected function maintenance(): void
    {
        $srs = app(ServiceRequestService::class);
        $wos = app(WorkOrderService::class);
        $tech = $this->techMap();
        $cases = $this->cases();
        $firstWo = [];
        foreach ($cases as $c) {
            [$day, $rep, $room, $name, $cat, $symptom, $impact, $end] = $c;
            $costs = $c[8] ?? [];
            $diag = $c[9] ?? null;
            $act = $c[10] ?? null;
            $block = ($c[11] ?? null) === 'block';
            $estimate = $c[12] ?? 0;
            $asset = $this->asset($room, $name);
            $loc = $asset?->location_id ?? Location::where('room_ref', $room)->orWhere('name', $room)->value('id');
            $this->at($day, mt_rand(7, 20), mt_rand(0, 59));
            $u = $this->as($rep);
            $sr = $this->try('lapor '.$room, fn () => $srs->create(array_filter(['asset_id' => $asset?->id, 'location_id' => $loc, 'category' => $cat, 'symptom' => $symptom, 'impact' => $impact, 'is_safety' => $impact === 'keselamatan']), $u));
            if (! $sr || $end === 'submitted') {
                continue;
            }
            $ce = $this->as('ce');
            Carbon::setTestNow(now()->addMinutes(mt_rand(5, 25)));
            if ($end === 'duplicate') {
                $orig = ServiceRequest::where('id', '<', $sr->id)->where('location_id', $sr->location_id)->orderByDesc('id')->first();
                $this->try('duplikat', fn () => $srs->reject($sr, 'Sama dengan laporan '.$orig?->number, $ce, $orig?->id));

                continue;
            }
            if ($end === 'rejected') {
                $this->try('tolak', fn () => $srs->reject($sr, $c[13] ?? 'Bukan kerusakan: remote dimintakan ke Housekeeping', $ce));

                continue;
            }
            $this->try('triase', fn () => $srs->triage($sr, ['priority' => $sr->priority, 'reason' => null], $ce));
            if ($end === 'triaged') {
                continue;
            }
            Carbon::setTestNow(now()->addMinutes(mt_rand(5, 30)));
            $wo = $this->try('buat WO', fn () => $srs->createWorkOrder([$sr->id], ['estimated_cost' => $estimate ?: array_sum(array_map(fn ($x) => $x[2] * $x[3], $costs)), 'title' => Str::limit($symptom, 60)], $ce));
            if (! $wo) {
                continue;
            }
            $firstWo[] = $wo->id;
            if ($block && ($asset?->location?->isRoom())) {
                $rb = $this->try('blokir', fn () => $wos->proposeRoomBlock($wo, $this->blockReason(), $ce));
                if ($rb) {
                    $this->as('fom');
                    Carbon::setTestNow(now()->addMinutes(20));
                    $rb->update(['status' => 'confirmed', 'started_at' => now(), 'fo_confirmed_by' => $this->u['fom']->id, 'fo_confirmed_at' => now()]);
                }
            }
            if ($end === 'awaiting_approval') {
                $this->try('ajukan', fn () => $wos->run($wo, 'submit', [], $ce));

                continue;
            }
            $wo = $this->approve($wo->fresh(), $wos) ?? $wo->fresh();
            if ($end === 'approved') {
                continue;
            }
            $t = $this->u[$tech[$cat] ?? 'tek1'];
            Carbon::setTestNow(now()->addMinutes(mt_rand(10, 40)));
            $wo = $this->try('tugaskan', fn () => $wos->run($wo->fresh(), 'assign', ['pic_id' => $t->id], $this->as('ce'), $wo->fresh()->version)) ?? $wo->fresh();
            if ($end === 'assigned') {
                continue;
            }
            Carbon::setTestNow(now()->addMinutes(mt_rand(15, 60)));
            $tu = $this->as($tech[$cat] ?? 'tek1');
            $wo = $this->try('mulai', fn () => $wos->run($wo->fresh(), 'start', [], $tu)) ?? $wo->fresh();
            foreach ($costs as [$type, $desc, $qty, $unit]) {
                $this->try('biaya', fn () => $wos->addCost($wo->fresh(), ['type' => $type, 'description' => $desc, 'quantity' => $qty, 'unit_cost' => $unit], $tu));
            }
            if ($end === 'in_progress') {
                continue;
            }
            if ($end === 'on_hold') {
                $this->try('tunda', fn () => $wos->run($wo->fresh(), 'hold', ['reason' => $c[13] ?? 'Menunggu suku cadang cartridge shower dari supplier'], $tu));

                continue;
            }
            Carbon::setTestNow(now()->addMinutes(mt_rand(40, 180)));
            $wo = $this->try('selesai', fn () => $wos->run($wo->fresh(), 'complete', array_filter([
                'diagnosis' => $diag, 'action_taken' => $act, 'function_test_result' => 'pass', 'root_cause' => $diag, 'failure_mode' => Str::limit((string) $diag, 60, ''),
                'evidence_exception_reason' => 'Data demo — foto bukti tidak disertakan', 'no_cost_reason' => $costs ? null : 'Tanpa material / jasa luar',
            ]), $tu)) ?? $wo->fresh();
            if ($end === 'completed') {
                continue;
            }
            Carbon::setTestNow(now()->addMinutes(mt_rand(20, 120)));
            $wo = $this->try('verifikasi', fn () => $wos->run($wo->fresh(), 'verify', array_filter(['asset_status_after' => 'serviceable', 'fo_confirmed' => $wo->room_access_needed ? true : null], fn ($v) => $v !== null), $this->as('ce'))) ?? $wo->fresh();
            if ($block) {
                RoomBlock::where('work_order_id', $wo->id)->update(['status' => 'released', 'ended_at' => now()]);
            }
            if ($end === 'verified') {
                continue;
            }
            Carbon::setTestNow(now()->addHours(2));
            $this->try('tutup', fn () => $wos->run($wo->fresh(), 'close', [], $this->as('ce')));
            if ($costs) {
                Carbon::setTestNow(now()->addDay());
                $this->try('final biaya', fn () => $wos->finalizeCosts($wo->fresh(), $this->as('fin')));
            }
        }

        // pekerjaan preventif (tanpa laporan): satu selesai bulan lalu, satu terjadwal minggu ini
        foreach ($this->pmPlan() as [$room, $name, $title, $done]) {
            $a = $this->asset($room, $name);
            foreach ([-28, -1] as $i => $day) {
                $this->at($day, 8);
                $ce = $this->as('ce');
                $wo = $this->try('PM', fn () => WorkOrder::create([
                    'type' => 'preventive', 'title' => $title, 'priority' => 'P4', 'asset_id' => $a?->id, 'location_id' => $a?->location_id,
                    'status' => 'draft', 'created_by' => $ce->id, 'received_at' => now(), 'estimated_cost' => 0,
                ]));
                if (! $wo) {
                    continue;
                }
                $wo = $this->try('PM setujui', fn () => $wos->run($wo, 'approve_direct', ['reason' => 'PM rutin'], $ce)) ?? $wo;
                $tk = $this->techMap()['listrik'] ?? 'tek2';
                $wo = $this->try('PM tugas', fn () => $wos->run($wo->fresh(), 'assign', ['pic_id' => $this->u[$tk]->id], $ce, $wo->fresh()->version)) ?? $wo;
                if ($i === 1) {
                    continue;
                }
                $t2 = $this->as($tk);
                $wo = $this->try('PM mulai', fn () => $wos->run($wo->fresh(), 'start', [], $t2)) ?? $wo;
                Carbon::setTestNow(now()->addHour());
                $wo = $this->try('PM selesai', fn () => $wos->run($wo->fresh(), 'complete', ['diagnosis' => 'Kondisi normal', 'action_taken' => $done, 'function_test_result' => 'pass', 'evidence_exception_reason' => 'Data demo', 'no_cost_reason' => 'PM rutin tanpa material'], $t2)) ?? $wo;
                $wo = $this->try('PM verif', fn () => $wos->run($wo->fresh(), 'verify', ['asset_status_after' => 'serviceable'], $this->as('ce'))) ?? $wo;
                $this->try('PM tutup', fn () => $wos->run($wo->fresh(), 'close', [], $this->as('ce')));
            }
        }
    }

    /** biaya > 0: diajukan Chief Engineer, disetujui Finance (≤ 10 jt) atau GM; tanpa biaya: setujui langsung */
    protected function approve(WorkOrder $wo, WorkOrderService $wos): ?WorkOrder
    {
        $ce = $this->as('ce');
        if ((float) $wo->estimated_cost <= 0) {
            return $this->try('setujui', fn () => $wos->run($wo, 'approve_direct', ['reason' => 'Perbaikan tanpa biaya'], $ce));
        }
        $wo = $this->try('ajukan', fn () => $wos->run($wo, 'submit', [], $ce)) ?? $wo;
        Carbon::setTestNow(now()->addMinutes(mt_rand(30, 180)));
        $who = (float) $wo->estimated_cost <= 10000000 && mt_rand(0, 1) ? 'fin' : 'gm';

        return $this->try('setujui', fn () => $wos->run($wo->fresh(), 'approve', [], $this->as($who)));
    }

    protected function transfers(): void
    {
        $svc = app(TransferService::class);
        $root = Location::whereIn('location_type', ['building', 'zone'])->whereNull('parent_id')->orderBy('id')->first();
        $gudang = Location::where('name', $this->warehouseName())->first() ?? Location::create([
            'property_id' => $this->p->id, 'parent_id' => $root?->id, 'name' => $this->warehouseName(), 'location_type' => 'area', 'is_active' => true,
            'code' => ($root?->code ?? 'X').'-GUDANG-ENG',
        ]);
        foreach ($this->transferPlan() as [$room, $name, $day, $reason, $finish]) {
            $a = $this->asset($room, $name);
            if (! $a) {
                $this->log[] = 'mutasi: aset tidak ditemukan '.$name;

                continue;
            }
            $this->at($day, 13);
            $ac = $this->as('ac');
            $t = AssetTransfer::create(['to_location_id' => $gudang->id, 'reason' => $reason, 'status' => 'draft', 'requested_by' => $ac->id]);
            $item = $t->items()->create(['asset_id' => $a->id, 'from_location_id' => $a->location_id, 'condition_out' => $a->condition_grade ?: 'B']);
            $this->try('mutasi ajukan', fn () => $svc->submit($t, $ac));
            if (! $finish) {
                continue;
            }
            Carbon::setTestNow(now()->addHours(2));
            $this->try('mutasi setujui', fn () => $svc->approve($t->fresh(), $this->as('gm')));
            $this->try('mutasi kirim', fn () => $svc->dispatch($t->fresh(), $this->as('ac')));
            Carbon::setTestNow(now()->addHour());
            $this->try('mutasi terima', fn () => $svc->receive($t->fresh(), [$item->id => 'E'], $this->as('ce')));
            $this->try('mutasi selesai', fn () => $svc->close($t->fresh(), $this->as('ac')));
        }
    }
}
