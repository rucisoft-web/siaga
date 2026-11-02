<?php

namespace App\Http\Controllers;

use App\Filament\Pages\Penyusutan;
use App\Services\DepreciationService;
use App\Support\CurrentProperty;
use App\Support\Fmt;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

/** Ekspor daftar aset tetap & usulan jurnal penyusutan ke Excel. */
class DepreciationExportController extends Controller
{
    public function export(Request $request)
    {
        $u = $request->user();
        abort_unless($u->hasPermission('asset.view_financial') && $u->hasPermission('report.export'), 403);
        $asOf = Penyusutan::asOf($request->query('periode'));
        $rows = (new DepreciationService)->register($asOf, $request->integer('kategori') ?: null, Penyusutan::locationScope($request->integer('lokasi') ?: null));
        $code = app(CurrentProperty::class)->get()?->code ?? 'SIAGA';
        $file = tempnam(sys_get_temp_dir(), 'dep').'.xlsx';
        $w = new Writer;
        $w->openToFile($file);
        $w->getCurrentSheet()->setName('Daftar aset tetap');
        $w->addRow(Row::fromValues(['Daftar aset tetap per '.$asOf->format('d-m-Y').' · '.$code]));
        $w->addRow(Row::fromValues(['Kode aset', 'Nama aset', 'Kategori', 'Lokasi', 'Ref. akuntansi', 'Tgl perolehan', 'Mulai susut', 'Metode', 'Masa manfaat (bln)', 'Bulan berjalan', 'Harga perolehan', 'Nilai residu', 'Beban bulan ini', 'Beban s.d. bulan ini (tahun berjalan)', 'Akumulasi penyusutan', 'Nilai buku', 'Habis disusutkan']));
        foreach ($rows as $r) {
            $a = $r['asset'];
            $d = $r['d'];
            $w->addRow(Row::fromValues([
                $a->asset_code, Fmt::csvSafe($a->name), $a->category?->name, Fmt::csvSafe($a->location?->full_path ?: $a->location?->name), Fmt::csvSafe($a->financial?->accounting_ref),
                $a->financial?->acquisition_date?->format('Y-m-d'), $d['start']?->format('Y-m'), DepreciationService::METHODS[$d['method']] ?? $d['method'], $d['life'], $d['months'],
                $d['cost'], $d['residual'], $d['period'], $d['ytd'], $d['accum'], $d['book'], $d['fully'] ? 'ya' : '',
            ]));
        }
        $w->addNewSheetAndMakeItCurrent()->setName('Jurnal');
        $w->addRow(Row::fromValues(['Usulan jurnal penyusutan '.$asOf->translatedFormat('F Y')]));
        $w->addRow(Row::fromValues(['Akun', 'Keterangan', 'Debit', 'Kredit']));
        foreach ($rows->groupBy(fn ($r) => $r['asset']->category_id) as $g) {
            $c = $g->first()['asset']->category;
            $amt = round($g->sum(fn ($r) => $r['d']['period']), 2);
            if ($amt <= 0) {
                continue;
            }
            $w->addRow(Row::fromValues([$c?->gl_expense_account ?: '(akun beban)', 'Beban penyusutan · '.$c?->name, $amt, null]));
            $w->addRow(Row::fromValues([$c?->gl_accum_account ?: '(akun akumulasi)', 'Akumulasi penyusutan · '.$c?->name, null, $amt]));
        }
        $w->close();

        return response()->download($file, 'siaga-penyusutan-'.$code.'-'.$asOf->format('Y-m').'.xlsx')->deleteFileAfterSend();
    }
}
