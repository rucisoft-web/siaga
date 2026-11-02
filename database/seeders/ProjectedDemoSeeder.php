<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Location;
use App\Services\MasterImportService;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Data demo perusahaan yang belum punya workbook master aset: struktur disusun sebagai PROYEKSI di kelas turunan
 * (tipe ruang, isi standar per tipe, area & estimasi alat, tata letak ruang), ditulis menjadi workbook berformat
 * "Master Aset" lalu diimpor lewat jalur yang sama dengan impor master sungguhan. Alur sesudahnya (cetak label,
 * pendataan, aktivasi, laporan, work order, mutasi) memakai GpchDemoSeeder.
 */
abstract class ProjectedDemoSeeder extends GpchDemoSeeder
{
    /** @return array<string, string> nama tipe ruang => gedung utama */
    abstract protected function roomTypes(): array;

    /** @return array<int, array{0:string,1:string,2:string,3:array<int,int>}> [kelompok, aset, satuan, jumlah per tipe (urutan roomTypes)] */
    abstract protected function roomItems(): array;

    /** @return array<int, array{0:string,1:string,2:string,3:string,4:string,5:int}> [gedung, area, aset, kategori, satuan, qty estimasi] */
    abstract protected function areaItems(): array;

    /** @return array<string, array{prefix:string, floors: array<int, array<string,int>>}> gedung => awalan nomor & [lantai => [tipe => jumlah]] */
    abstract protected function layout(): array;

    /** @return array<int, array{0:string,1:string}> [nama ruang meeting / aula, gedung] */
    protected function venues(): array
    {
        return [];
    }

    /** Lokasi PROYEKSI ditulis ke workbook lalu diimpor (jalur sama dengan menu Impor master aset). */
    protected function setupData(): void
    {
        $path = $this->workbookPath();
        @mkdir(dirname($path), 0775, true);
        $this->writeWorkbook($path);
        app(MasterImportService::class)->import($path, $this->u['ce'], false, true);
        $this->afterImport();
    }

    public function workbookPath(): string
    {
        return storage_path('app/demo/'.strtolower($this->code()).'-master-aset.xlsx');
    }

    protected function afterImport(): void {}

    protected function rooms(): void {}

    public function writeWorkbook(string $path): void
    {
        $types = array_keys($this->roomTypes());
        $w = new Writer;
        $w->openToFile($path);
        $sheet = function (string $name, array $rows, bool $first = false) use ($w) {
            if (! $first) {
                $w->addNewSheetAndMakeItCurrent();
            }
            $w->getCurrentSheet()->setName($name);
            foreach ($rows as $r) {
                $w->addRow(Row::fromValues($r));
            }
        };
        $sheet('Daftar Jenis Kamar', array_merge([['Jenis kamar', 'Alokasi gedung', 'Jumlah']], array_map(fn ($t, $b) => [$t, $b, $this->countType($t)], $types, $this->roomTypes())), true);
        $ref = [array_merge(['Kelompok', 'Aset', 'Satuan'], $types)];
        foreach ($this->roomItems() as [$group, $name, $unit, $qty]) {
            $ref[] = array_merge([$group, $name, $unit], array_map(fn ($i) => (int) ($qty[$i] ?? 0), array_keys($types)));
        }
        $sheet('Rujukan Aset Kamar', $ref);
        if ($this->venues()) {
            $sheet('Nama Ruang Meeting', array_merge([['Nama ruangan', 'Perkiraan gedung']], $this->venues()));
        }
        $venueNames = array_map(fn ($v) => $v[0], $this->venues());
        $est = [['Jenis sumber', 'Nama aset', 'Area / ruangan', 'Gedung / zona', 'Satuan', 'Kategori', 'Qty estimasi']];
        foreach ($this->areaItems() as [$bld, $area, $name, $cat, $unit, $qty]) {
            $est[] = [in_array($area, $venueNames, true) ? 'Ruang meeting' : 'Area umum', $name, $area, $bld, $unit, $cat, $qty];
        }
        $sheet('Master Aset Gabungan', $est);
        $rooms = [['Nomor kamar', 'Jenis kamar', 'Gedung', 'Lantai']];
        foreach ($this->roomList() as $r) {
            $rooms[] = [$r['no'], $r['type'], $r['building'], (string) $r['floor']];
        }
        $sheet('Daftar Kamar', $rooms);
        $w->close();
    }

    /** @return array<int, array{no:string,type:string,building:string,floor:int}> nomor ruang = awalan + lantai×100 + urut */
    protected function roomList(): array
    {
        $out = [];
        foreach ($this->layout() as $bld => $cfg) {
            foreach ($cfg['floors'] as $fl => $mix) {
                $i = 0;
                foreach ($mix as $type => $n) {
                    for ($k = 0; $k < $n; $k++) {
                        $out[] = ['no' => $cfg['prefix'].($fl * 100 + (++$i)), 'type' => $type, 'building' => $bld, 'floor' => $fl];
                    }
                }
            }
        }

        return $out;
    }

    protected function countType(string $type): int
    {
        return count(array_filter($this->roomList(), fn ($r) => $r['type'] === $type));
    }

    /** "Gedung > Area" menunjuk area di gedung tertentu (nama area boleh sama di beberapa gedung). */
    protected function asset(string $room, string $name): ?Asset
    {
        if (! str_contains($room, ' > ')) {
            return parent::asset($room, $name);
        }
        [$b, $a] = explode(' > ', $room, 2);
        $loc = Location::where('name', $a)->whereHas('parent', fn ($q) => $q->where('name', $b))->pluck('id');

        return Asset::whereIn('location_id', $loc)->where('name', 'like', $name.'%')->orderBy('id')->first();
    }
}
