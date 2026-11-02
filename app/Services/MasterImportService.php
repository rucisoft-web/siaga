<?php

namespace App\Services;

use App\Models\AssetCategory;
use App\Models\AssetQuantityGroup;
use App\Models\Location;
use App\Models\RoomAssetTemplate;
use App\Models\RoomType;
use App\Models\User;
use App\Support\CurrentProperty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Impor workbook "Master Aset" (format GPCH) untuk memulai pendataan:
 *  - Daftar Jenis Kamar      → tipe kamar
 *  - Rujukan Aset Kamar      → template isi kamar per tipe (jenis & jumlah per kamar)
 *  - Nama Ruang Meeting      → ruang meeting (venue) per gedung
 *  - Master Aset Gabungan    → area umum & ruang meeting + item ESTIMASI (kelompok hitungan, jumlah fisik belum dihitung)
 *  - Daftar Kamar (opsional) → nomor kamar per tipe/gedung/lantai
 * Baris "Aset Kamar" di master tidak diimpor: isinya sudah tercakup template × kamar.
 * Aman diulang: data yang sudah ada (nama/kode sama) dilewati.
 */
class MasterImportService
{
    /** satuan / kelompok yang dihitung jumlahnya saja (tanpa label QR per unit) */
    public const QUANTITY_UNITS = ['titik', 'set', 'pcs', 'lot', 'par', 'buku', 'roll', 'pasang'];

    public const QUANTITY_GROUPS = ['linen'];

    public const LABELS = [
        'tipe_kamar' => 'Tipe kamar baru', 'template' => 'Template isi kamar', 'item_template' => 'Item dalam template',
        'gedung' => 'Gedung / zona baru', 'ruang' => 'Area umum baru', 'ruang_meeting' => 'Ruang meeting baru', 'kamar' => 'Kamar baru',
        'item_estimasi' => 'Item estimasi (dihitung saat pendataan)', 'kategori' => 'Kategori baru',
        'baris_kamar_dilewati' => 'Baris "Aset Kamar" (sudah tercakup template)', 'dilewati_sudah_ada' => 'Dilewati karena sudah ada',
    ];

    protected int $pid;

    protected array $report;

    protected array $warnings = [];

    /** @return array<string, array<int, array<string, mixed>>> nama sheet => baris (berkunci judul kolom) */
    public function read(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);
        $out = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $head = null;
            $orig = [];
            $rows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $vals = array_map(fn ($c) => is_string($c) ? trim($c) : $c, $row->toArray());
                if ($head === null) {
                    if (count(array_filter($vals, fn ($v) => $v !== null && $v !== '')) >= 2) {
                        $orig = array_map(fn ($h) => trim(preg_replace('/\s+/u', ' ', (string) $h)), $vals);
                        $head = array_map(fn ($h) => Str::lower($h), $orig);
                    }

                    continue;
                }
                if (! array_filter($vals, fn ($v) => $v !== null && $v !== '')) {
                    continue;
                }
                $r = [];
                foreach ($head as $i => $h) {
                    if ($h !== '') {
                        $r[$h] = $vals[$i] ?? null;
                    }
                }
                $r['_raw'] = $vals;
                $rows[] = $r;
            }
            $out[trim($sheet->getName())] = ['head' => $head ?? [], 'orig_head' => $orig, 'rows' => $rows];
        }
        $reader->close();

        return $out;
    }

    /**
     * @return array{summary: array<string,int>, warnings: array<int,string>, dry_run: bool}
     */
    public function import(string $path, User $user, bool $dryRun = false, bool $publish = false): array
    {
        if (! $user->hasPermission('template.manage')) {
            throw WorkflowException::forbidden('Impor master aset butuh hak kelola template.');
        }
        $book = $this->read($path);
        $sheet = fn (string $name) => collect($book)->first(fn ($v, $k) => Str::lower($k) === Str::lower($name));
        if (! $sheet('Rujukan Aset Kamar') && ! $sheet('Master Aset Gabungan')) {
            throw WorkflowException::invalid('Format tidak dikenali. Workbook harus punya sheet "Rujukan Aset Kamar" dan/atau "Master Aset Gabungan".');
        }

        $this->pid = (int) app(CurrentProperty::class)->id();
        $this->warnings = [];
        $this->report = ['tipe_kamar' => 0, 'template' => 0, 'item_template' => 0, 'gedung' => 0, 'ruang' => 0, 'ruang_meeting' => 0, 'kamar' => 0, 'item_estimasi' => 0, 'kategori' => 0, 'baris_kamar_dilewati' => 0, 'dilewati_sudah_ada' => 0];

        DB::beginTransaction();
        try {
            $types = $this->roomTypes($sheet('Daftar Jenis Kamar'), $sheet('Rujukan Aset Kamar'));
            if ($s = $sheet('Rujukan Aset Kamar')) {
                $this->templates($s, $types, $user, $publish);
            }
            if ($s = $sheet('Nama Ruang Meeting')) {
                foreach ($s['rows'] as $r) {
                    $name = $this->clean($r['nama ruangan'] ?? null);
                    if ($name) {
                        $this->area($name, $this->clean($r['perkiraan gedung'] ?? null) ?: 'Belum dipastikan', 'venue');
                    }
                }
            }
            if ($s = $sheet('Master Aset Gabungan')) {
                $this->estimates($s);
            }
            if ($s = $sheet('Daftar Kamar')) {
                $this->rooms($s, $types);
            } elseif ($types) {
                $this->warnings[] = 'Nomor kamar belum ada (sheet "Daftar Kamar" tidak ditemukan). Tambahkan sheet Daftar Kamar: Nomor kamar | Jenis kamar | Gedung | Lantai, lalu impor ulang.';
            }
            $dryRun ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return ['summary' => $this->report, 'warnings' => array_values(array_unique($this->warnings)), 'dry_run' => $dryRun];
    }

    /**
     * Tambah kamar dari daftar (Nomor kamar, Jenis kamar, Gedung, Lantai) tanpa workbook — dipakai data demo.
     * @param array<int, array{nomor kamar:string, jenis kamar:string, gedung:string, lantai:string}> $rows
     */
    public function addRooms(array $rows): int
    {
        $this->pid = (int) app(CurrentProperty::class)->id();
        $this->report = array_fill_keys(array_keys(self::LABELS), 0);
        $types = RoomType::all()->keyBy('name')->all();
        $this->rooms(['rows' => $rows], $types);

        return $this->report['kamar'];
    }

    // ------------------------------------------------------------------ tipe kamar & template

    /** @return array<string, RoomType> nama jenis kamar => tipe */
    protected function roomTypes(?array $list, ?array $ref): array
    {
        $names = [];
        foreach ($list['rows'] ?? [] as $r) {
            $n = $this->clean($r['jenis kamar'] ?? null);
            if ($n && ! Str::startsWith(Str::lower($n), 'total')) {
                $names[$n] = $this->clean($r['alokasi gedung'] ?? null);
            }
        }
        foreach ($this->typeColumns($ref) as $n) {
            $names[$n] ??= null;
        }
        $out = [];
        foreach (array_keys($names) as $n) {
            $t = RoomType::where('name', $n)->first();
            if (! $t) {
                $t = RoomType::create([
                    'property_id' => $this->pid, 'code' => $this->uniqueCode(RoomType::class, Str::upper(Str::slug($n)), 20),
                    'name' => $n, 'bed_config' => Str::contains(Str::lower($n), 'twin') ? 'Twin' : (Str::contains(Str::lower($n), 'suite') ? 'Suite' : 'King'),
                ]);
                $this->report['tipe_kamar']++;
            }
            $out[$n] = $t;
        }

        return $out;
    }

    /** judul kolom tipe kamar di sheet rujukan (setelah Kelompok, Aset, Satuan), dengan huruf asli */
    protected function typeColumns(?array $ref): array
    {
        if (! $ref) {
            return [];
        }

        return collect($ref['orig_head'])->slice(3)->filter()->values()->all();
    }

    protected function templates(array $ref, array $types, User $user, bool $publish): void
    {
        $head = $ref['head'];
        $typeByLower = collect($types)->keyBy(fn ($t, $n) => Str::lower($n));
        foreach (array_slice($head, 3, null, true) as $col => $h) {
            $type = $typeByLower->get($h);
            if (! $type || $h === '') {
                continue;
            }
            $code = 'TPL-'.$type->code;
            $tpl = RoomAssetTemplate::where('room_type_id', $type->id)->orderByDesc('version')->first();
            if ($tpl && $tpl->items()->exists()) {
                $this->report['dilewati_sudah_ada']++;

                continue;
            }
            $tpl ??= RoomAssetTemplate::create([
                'property_id' => $this->pid, 'code' => mb_substr($code, 0, 30), 'version' => 1, 'name' => 'Isi standar '.$type->name,
                'room_type_id' => $type->id, 'status' => 'draft', 'prepared_by' => $user->id,
                'notes' => 'Diimpor dari master aset (estimasi). Periksa jumlah & jenis sebelum diterbitkan; kondisi aktual dicek saat pendataan.',
            ]);
            $this->report['template']++;
            $sort = 0;
            $keys = [];
            foreach ($ref['rows'] as $r) {
                $raw = $r['_raw'];
                $group = $this->clean($raw[0] ?? null);
                $name = $this->clean($raw[1] ?? null);
                $unit = Str::lower($this->clean($raw[2] ?? null) ?: 'unit');
                $qty = (int) round((float) ($raw[$col] ?? 0));
                if (! $name || $qty <= 0) {
                    continue;
                }
                $key = mb_substr(Str::slug($name), 0, 40) ?: 'item-'.($sort + 1);
                while (in_array($key, $keys, true)) {
                    $key = mb_substr($key, 0, 36).'-'.count($keys);
                }
                $keys[] = $key;
                $mode = $this->isQuantity($group, $unit) ? 'quantity' : 'individual';
                $tpl->items()->create([
                    'item_key' => $key, 'category_id' => $this->category($group ?: 'Lain-lain', 'kamar', $mode)->id, 'standard_name' => mb_substr($name, 0, 190),
                    'quantity' => min($qty, 999), 'mode' => $mode, 'unit' => mb_substr($unit, 0, 20), 'sort' => ++$sort,
                ]);
                $this->report['item_template']++;
            }
            if ($publish && $sort > 0) {
                $tpl->update(['status' => 'published', 'published_at' => now(), 'reviewed_by' => $user->id]);
            }
        }
    }

    protected function isQuantity(?string $group, string $unit): bool
    {
        $g = Str::lower((string) $group);

        return in_array($unit, self::QUANTITY_UNITS, true) || collect(self::QUANTITY_GROUPS)->contains(fn ($q) => $g === $q);
    }

    // ------------------------------------------------------------------ lokasi

    protected function building(string $name): Location
    {
        $b = Location::whereIn('location_type', ['building', 'zone'])->where('name', $name)->first();
        if ($b) {
            return $b;
        }
        $initials = collect(preg_split('/[^\pL\pN]+/u', $name))->filter()->map(fn ($w) => Str::upper(mb_substr($w, 0, 1)))->join('');
        $this->report['gedung']++;

        return Location::create([
            'property_id' => $this->pid, 'code' => $this->uniqueCode(Location::class, $initials ?: 'GD', 12), 'name' => $name,
            'location_type' => in_array(Str::lower($name), ['main building', 'west wing', 'west building', 'gedung utama'], true) || Str::contains(Str::lower($name), ['building', 'gedung', 'wing', 'tower']) ? 'building' : 'zone',
            'is_active' => true,
        ]);
    }

    protected function area(string $name, string $buildingName, string $type = 'area'): Location
    {
        // ruang meeting unik per nama; area umum per gedung (mis. "Lobby" di Main Building dan West Wing adalah dua ruang)
        if ($type === 'venue' && ($v = Location::where('location_type', 'venue')->where('name', $name)->first())) {
            return $v;
        }
        $b = $this->building($buildingName);
        if ($existing = Location::whereIn('location_type', ['area', 'venue'])->where('parent_id', $b->id)->where('name', $name)->first()) {
            return $existing;
        }
        $this->report[$type === 'venue' ? 'ruang_meeting' : 'ruang']++;

        return Location::create([
            'property_id' => $this->pid, 'parent_id' => $b->id, 'name' => mb_substr($name, 0, 190), 'location_type' => $type, 'is_active' => true,
            'code' => $this->uniqueCode(Location::class, $b->code.'-'.mb_substr(Str::upper(Str::slug($name)), 0, 24), 40),
        ]);
    }

    protected function rooms(array $s, array $types): void
    {
        $typeByLower = collect($types)->keyBy(fn ($t, $n) => Str::lower($n));
        foreach ($s['rows'] as $r) {
            $no = $this->clean($r['nomor kamar'] ?? $r['nomor'] ?? $r['no kamar'] ?? null);
            if (! $no || Str::startsWith(Str::lower($no), '(contoh')) {
                continue;
            }
            $no = preg_replace('/\.0$/', '', $no);
            $type = $typeByLower->get(Str::lower((string) $this->clean($r['jenis kamar'] ?? null)));
            if (! $type) {
                $this->warnings[] = 'Kamar '.$no.': jenis kamar "'.($r['jenis kamar'] ?? '').'" tidak dikenal, dilewati.';

                continue;
            }
            $b = $this->building($this->clean($r['gedung'] ?? null) ?: 'Gedung Utama');
            $fl = preg_replace('/\.0$/', '', (string) ($this->clean($r['lantai'] ?? null) ?: mb_substr($no, 0, max(1, mb_strlen($no) - 2))));
            $floor = Location::where('parent_id', $b->id)->where('location_type', 'floor')->where('floor_label', $fl)->first()
                ?? Location::create(['property_id' => $this->pid, 'parent_id' => $b->id, 'location_type' => 'floor', 'floor_label' => mb_substr($fl, 0, 20),
                    'name' => 'Lantai '.$fl, 'code' => $this->uniqueCode(Location::class, $b->code.'-L'.Str::upper(Str::slug($fl)), 40), 'is_active' => true]);
            if (Location::where('location_type', 'room')->where('room_ref', $no)->exists()) {
                $this->report['dilewati_sudah_ada']++;

                continue;
            }
            Location::create([
                'property_id' => $this->pid, 'parent_id' => $floor->id, 'location_type' => 'room', 'room_ref' => mb_substr($no, 0, 20),
                'room_type_id' => $type->id, 'name' => 'Kamar '.$no, 'code' => $this->uniqueCode(Location::class, $b->code.'-'.Str::upper(Str::slug($no)), 40), 'is_active' => true,
            ]);
            $this->report['kamar']++;
        }
    }

    // ------------------------------------------------------------------ estimasi area umum

    protected function estimates(array $s): void
    {
        $meetingNames = Location::where('location_type', 'venue')->pluck('name')->map(fn ($n) => Str::lower($n))->all();
        foreach ($s['rows'] as $r) {
            $source = Str::lower((string) $this->clean($r['jenis sumber'] ?? null));
            if ($source === 'aset kamar' || $this->clean($r['jenis kamar (jika ada)'] ?? null)) {
                $this->report['baris_kamar_dilewati']++;

                continue;
            }
            $name = $this->clean($r['nama aset'] ?? null);
            $areaName = $this->clean($r['area / ruangan'] ?? null) ?: 'Umum';
            if (! $name) {
                continue;
            }
            $type = $source === 'ruang meeting' || in_array(Str::lower($areaName), $meetingNames, true) ? 'venue' : 'area';
            $loc = $this->area($areaName, $this->clean($r['gedung / zona'] ?? null) ?: 'Belum dipastikan', $type);
            $unit = Str::lower($this->clean($r['satuan'] ?? null) ?: 'unit');
            $catName = $this->clean($r['kategori'] ?? null) ?: 'Lain-lain';
            if (AssetQuantityGroup::where('location_id', $loc->id)->where('name', mb_substr($name, 0, 190))->exists()) {
                $this->report['dilewati_sudah_ada']++;

                continue;
            }
            $qty = is_numeric($r['qty estimasi'] ?? null) ? (float) $r['qty estimasi'] : null;
            AssetQuantityGroup::create([
                'property_id' => $this->pid, 'location_id' => $loc->id, 'category_id' => $this->category($catName, $type === 'venue' ? 'mice' : null, 'quantity')->id,
                'name' => mb_substr($name, 0, 190), 'unit' => mb_substr($unit, 0, 20), 'expected_qty' => $qty, 'on_hand' => null, 'status' => 'draft',
            ]);
            $this->report['item_estimasi']++;
        }
    }

    // ------------------------------------------------------------------ util

    protected function category(string $name, ?string $group, string $mode): AssetCategory
    {
        $c = AssetCategory::whereRaw('lower(name) = ?', [Str::lower($name)])->first();
        if ($c) {
            return $c;
        }
        $this->report['kategori']++;

        return AssetCategory::create([
            'property_id' => $this->pid, 'code' => $this->uniqueCode(AssetCategory::class, Str::upper(Str::slug($name)), 30), 'name' => mb_substr($name, 0, 190),
            'group' => $group, 'default_criticality' => 'medium', 'tracking_mode' => $mode === 'quantity' && Str::contains(Str::lower($name), 'linen') ? 'quantity' : 'individual', 'is_active' => true,
        ]);
    }

    protected function uniqueCode(string $model, string $base, int $max): string
    {
        $base = trim(mb_substr($base ?: 'X', 0, $max), '-') ?: 'X';
        $code = $base;
        $i = 2;
        while ($model::where('code', $code)->exists()) {
            $suffix = '-'.$i++;
            $code = mb_substr($base, 0, $max - strlen($suffix)).$suffix;
        }

        return $code;
    }

    protected function clean(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }
        $v = trim(preg_replace('/\s+/u', ' ', (string) $v));

        return $v === '' ? null : $v;
    }
}
