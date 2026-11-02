<?php

namespace Database\Seeders;

use App\Models\ApprovalLimit;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLabel;
use App\Models\Department;
use App\Models\Location;
use App\Models\Property;
use App\Models\Role;
use App\Models\RoomAssetTemplate;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Vendor;
use App\Support\CurrentProperty;
use Illuminate\Database\Seeder;

/**
 * Data contoh pilot (TERPISAH dari produksi): satu lantai kamar, area kitchen, MICE & utilitas,
 * template Deluxe (6 jenis / 7 unit), akun jabatan GPCH. Struktur & jumlah kamar wajib diverifikasi GPCH.
 */
class DemoSeeder extends Seeder
{
    public const PASSWORD = 'GPCH-2026!ganti';

    public function run(): void
    {
        $p = Property::where('code', config('assetcare.property_code', 'GPCH'))->firstOrFail();
        app(CurrentProperty::class)->set($p);
        $dept = fn ($code) => Department::withoutGlobalScopes()->where('property_id', $p->id)->where('code', $code)->value('id');
        $cat = fn ($code) => AssetCategory::withoutGlobalScopes()->where('property_id', $p->id)->where('code', $code)->value('id');

        // ---- pengguna (nama/jabatan mengikuti data Arah GPCH; email usulan — konfirmasi dengan HR)
        $people = [
            ['edp@grandpasundanhotel.com', 'Admin EDP GPCH (isi nama)', 'EDP / IT Manager', 'EDP', ['it-admin']],
            ['gm@grandpasundanhotel.com', 'Ibu Vivera', 'General Manager', 'EXE', ['gm']],
            ['owner@grandpasundanhotel.com', 'Bapak Wimpy', 'Pemilik', 'EXE', ['owner']],
            ['yodi@grandpasundanhotel.com', 'Bapak Yodi S. Ibrahim', 'Direksi', 'EXE', ['owner-rep']],
            ['ca@grandpasundanhotel.com', 'Bapak Indra', 'Chief Accountant', 'ACC', ['finance']],
            ['engineering@grandpasundanhotel.com', 'Bapak Wahyudin', 'Chief Engineer (Plt.)', 'ENG', ['chief-engineering']],
            ['eng.admin@grandpasundanhotel.com', 'Rara', 'Engineering Admin', 'ENG', ['asset-controller']],
            ['teknisi1@grandpasundanhotel.com', 'Teknisi 1 (isi nama)', 'Teknisi Engineering', 'ENG', ['technician']],
            ['teknisi2@grandpasundanhotel.com', 'Teknisi 2 (isi nama)', 'Teknisi Engineering', 'ENG', ['technician']],
            ['fom@grandpasundanhotel.com', 'Bapak Rizki', 'Front Office Manager', 'FO', ['hod', 'front-office']],
            ['fo.supervisor@grandpasundanhotel.com', 'Muhamad Ihsan', 'Front Office Supervisor', 'FO', ['reporter', 'front-office']],
            ['hk@grandpasundanhotel.com', 'Ibu Wina', 'Executive Housekeeper (Plt.)', 'HK', ['hod']],
            ['hk.supervisor@grandpasundanhotel.com', 'Ujang Nur M', 'Housekeeping Supervisor', 'HK', ['reporter']],
            ['fbm@grandpasundanhotel.com', 'Bapak Asep Agus Damil Arifin', 'F&B Manager / Executive Chef', 'KIT', ['hod']],
            ['purchasing@grandpasundanhotel.com', 'Bapak Daniel', 'Purchasing', 'PUR', ['purchasing']],
            ['wiwin@visiperencana.id', 'Bapak I.G. Wiwin Suyasa', 'Auditor Operasional', 'EXE', ['auditor']],
        ];
        $roles = Role::pluck('id', 'slug');
        foreach ($people as [$email, $name, $title, $d, $r]) {
            $u = User::firstOrCreate(['email' => $email], [
                'name' => $name, 'job_title' => $title, 'password' => self::PASSWORD, 'property_id' => $p->id,
                'department_id' => $dept($d), 'is_active' => true, 'must_change_password' => true,
            ]);
            $u->roles()->syncWithoutDetaching(collect($r)->mapWithKeys(fn ($slug) => [$roles[$slug] => ['property_id' => $p->id]])->all());
        }
        User::whereIn('email', ['teknisi1@grandpasundanhotel.com', 'teknisi2@grandpasundanhotel.com'])->update(['labor_rate' => 35000]);

        // ---- batas persetujuan CONTOH (nominal wajib disahkan manajemen sebelum produksi)
        $gm = User::where('email', 'gm@grandpasundanhotel.com')->first();
        $ce = User::where('email', 'engineering@grandpasundanhotel.com')->first();
        $ca = User::where('email', 'ca@grandpasundanhotel.com')->first();
        foreach ([[$ce, 2500000], [$gm, 25000000], [$ca, 10000000]] as [$u, $max]) {
            ApprovalLimit::withoutGlobalScopes()->firstOrCreate(['property_id' => $p->id, 'user_id' => $u->id, 'object_type' => 'work_order'], [
                'max_amount' => $max, 'valid_from' => now()->startOfYear(), 'note' => 'CONTOH — nominal wajib disahkan manajemen',
            ]);
        }

        // ---- tipe kamar & lokasi pilot
        $dlx = RoomType::withoutGlobalScopes()->firstOrCreate(['property_id' => $p->id, 'code' => 'DLX'], ['name' => 'Deluxe (contoh)', 'bed_config' => 'King']);
        RoomType::withoutGlobalScopes()->firstOrCreate(['property_id' => $p->id, 'code' => 'DLX-TW'], ['name' => 'Deluxe Twin (contoh)', 'bed_config' => 'Twin']);
        RoomType::withoutGlobalScopes()->firstOrCreate(['property_id' => $p->id, 'code' => 'STE'], ['name' => 'Suite (contoh)', 'bed_config' => 'King']);

        $loc = function (string $code, string $name, string $type, ?Location $parent = null, array $extra = []) use ($p) {
            return Location::withoutGlobalScopes()->updateOrCreate(['property_id' => $p->id, 'code' => $code], $extra + [
                'name' => $name, 'location_type' => $type, 'parent_id' => $parent?->id, 'is_active' => true,
            ]);
        };
        $gu = $loc('GU', 'Gedung Utama', 'building');
        $l1 = $loc('GU-L1', 'Lantai 1', 'floor', $gu, ['floor_label' => '1']);
        $l3 = $loc('GU-L3', 'Lantai 3 (pilot)', 'floor', $gu, ['floor_label' => '3']);
        foreach (range(301, 310) as $no) {
            $loc('GU-L3-'.$no, 'Kamar '.$no, 'room', $l3, ['room_type_id' => $dlx->id, 'room_ref' => (string) $no, 'pms_room_status' => 'vacant']);
        }
        $lobby = $loc('GU-L1-LOBBY', 'Lobby', 'area', $l1);
        $resto = $loc('GU-L1-RESTO', 'Restoran', 'area', $l1);
        $kitchen = $loc('GU-L1-KIT', 'Kitchen Utama', 'area', $l1);
        $pastry = $loc('GU-L1-PASTRY', 'Pastry', 'area', $l1);
        $mice = $loc('MICE', 'Zona MICE', 'zone');
        $dome = $loc('MICE-DOME', 'Malibu Dome', 'venue', $mice);
        $mep = $loc('MEP', 'Zona MEP & utilitas', 'zone');
        $genset = $loc('MEP-GENSET', 'Ruang Genset', 'area', $mep);
        $boiler = $loc('MEP-BOILER', 'Ruang Boiler', 'area', $mep);
        $panel = $loc('MEP-PANEL', 'Ruang Panel Utama', 'area', $mep);

        // ---- template Deluxe: 6 jenis, 7 unit per kamar (contoh rancangan, bukan hasil inventarisasi)
        $tpl = RoomAssetTemplate::withoutGlobalScopes()->firstOrCreate(['property_id' => $p->id, 'code' => 'TPL-DLX', 'version' => 1], [
            'name' => 'Perlengkapan standar Deluxe', 'room_type_id' => $dlx->id, 'status' => 'draft',
            'prepared_by' => User::where('email', 'eng.admin@grandpasundanhotel.com')->value('id'),
            'notes' => 'Contoh dari spesifikasi bagian 24. Jumlah, merek & perlengkapan aktual tetap diperiksa di lapangan.',
        ]);
        if ($tpl->items()->doesntExist()) {
            $hk = $dept('HK');
            $eng = $dept('ENG');
            $items = [
                ['ac', 'AC-SPLIT', 'AC kamar', 1, null, $eng, 'medium', 90],
                ['tv-utama', 'TV', 'TV utama', 1, null, $eng, 'low', null],
                ['minibar', 'MINIBAR', 'Minibar', 1, null, $eng, 'low', 180],
                ['telepon', 'TEL', 'Telepon kamar', 1, null, $eng, 'low', null],
                ['bed', 'BED', 'Tempat tidur', 1, null, $hk, 'low', 365],
                ['lampu-meja', 'LAMP', 'Lampu meja', 2, 'Kiri, Kanan', $hk, 'low', null],
            ];
            foreach ($items as $i => [$key, $c, $name, $qty, $pos, $d, $crit, $pm]) {
                $tpl->items()->create(['item_key' => $key, 'category_id' => $cat($c), 'standard_name' => $name, 'quantity' => $qty, 'mode' => 'individual', 'unit' => 'unit', 'positions' => $pos, 'default_department_id' => $d, 'default_criticality' => $crit, 'pm_interval_days' => $pm, 'sort' => $i + 1]);
            }
            $tpl->forceFill(['status' => 'published', 'published_at' => now(), 'reviewed_by' => $ce->id])->saveQuietly();
        }

        Vendor::withoutGlobalScopes()->firstOrCreate(['property_id' => $p->id, 'code' => 'V-AC'], ['name' => 'Vendor servis AC (contoh)', 'specialization' => 'AC split & HVAC', 'service_hours' => '08.00–17.00']);
        Vendor::withoutGlobalScopes()->firstOrCreate(['property_id' => $p->id, 'code' => 'V-LIFT'], ['name' => 'Vendor lift (contoh)', 'specialization' => 'Pemeliharaan lift', 'service_hours' => '24 jam (darurat)']);

        // ---- beberapa aset utilitas & kitchen terverifikasi + satu TV lama di kamar 305 (uji pencocokan AC20)
        $mk = function (array $a) use ($p) {
            if ($found = Asset::withoutGlobalScopes()->where('property_id', $p->id)->where('legacy_code', $a['legacy_code'])->first()) {
                return $found;
            }
            $asset = Asset::create($a + ['property_id' => $p->id, 'ownership' => 'milik', 'operational_status' => 'serviceable', 'lifecycle_status' => 'draft', 'verification_status' => 'unverified']);
            AssetLabel::issueFor($asset);

            return $asset;
        };
        $rows = [
            ['GENSET', 'Genset utama', $genset, 'ENG', 'B', 'critical', 'LEG-GEN-01'],
            ['BOILER', 'Boiler air panas', $boiler, 'ENG', 'C', 'critical', 'LEG-BOI-01'],
            ['PANEL', 'Panel LVMDP', $panel, 'ENG', 'B', 'critical', 'LEG-PNL-01'],
            ['CHILL', 'Chiller kitchen 1', $kitchen, 'KIT', 'B', 'high', 'LEG-CHL-01'],
            ['FREEZ', 'Freezer pastry', $pastry, 'KIT', 'C', 'high', 'LEG-FRZ-01'],
            ['PROJ', 'Projector Malibu Dome', $dome, 'SM', 'B', 'medium', 'LEG-PRJ-01'],
            ['SOUND', 'Sound system Malibu Dome', $dome, 'SM', 'B', 'medium', 'LEG-SND-01'],
            ['APAR', 'APAR lobby 01', $lobby, 'SEC', 'A', 'critical', 'LEG-APR-01'],
            ['CCTV', 'CCTV lobby 01', $lobby, 'SEC', 'A', 'high', 'LEG-CTV-01'],
        ];
        foreach ($rows as [$c, $name, $l, $d, $cond, $crit, $legacy]) {
            $a = $mk(['name' => $name, 'category_id' => $cat($c), 'location_id' => $l->id, 'department_id' => $dept($d), 'condition_grade' => $cond, 'criticality' => $crit, 'legacy_code' => $legacy, 'custodian_id' => $ce->id, 'migration_source' => 'Contoh data awal']);
            if ($a->lifecycle_status === 'draft') {
                $a->forceFill(['verification_status' => 'verified', 'verified_by' => $ce->id, 'verified_at' => now()])->save();
                $a->forceFill(['lifecycle_status' => 'active'])->save();
            }
        }
        $mk(['name' => 'TV lama kamar 305', 'category_id' => $cat('TV'), 'location_id' => Location::withoutGlobalScopes()->where('code', 'GU-L3-305')->value('id'), 'department_id' => $dept('ENG'), 'brand' => 'Samsung', 'serial_number' => 'SN-TV-305-LAMA', 'legacy_code' => 'LEG-TV-305', 'migration_source' => 'Register lama']);
    }
}
