<?php

namespace App\Http\Controllers;

use App\Filament\Pages\PenghapusanAset;
use App\Filament\Pages\StockOpname;
use App\Models\Disposal;
use App\Models\StockTake;
use App\Services\StockTakeService;
use App\Support\CurrentProperty;

/** Berita acara siap cetak: stock opname & penghapusan aset. */
class ControlReportController extends Controller
{
    public function stockTake(StockTake $take, StockTakeService $svc)
    {
        abort_unless(StockOpname::canAccess(), 403);
        $items = fn (string $r) => $take->items()->with(['asset:id,asset_code,name', 'expectedLocation:id,full_path,name', 'foundLocation:id,full_path,name'])->where('result', $r)->get();

        return view('reports.berita-acara-opname', [
            'take' => $take->load('location', 'creator', 'closer'), 'sum' => $svc->summary($take), 'property' => app(CurrentProperty::class)->get(),
            'notFound' => $items('not_found'), 'moved' => $items('moved'), 'unregistered' => $items('unregistered'),
            'byLocation' => $take->items()->join('locations', 'locations.id', '=', 'stock_take_items.expected_location_id')
                ->selectRaw("locations.full_path as loc, count(*) as n, sum(result in ('found','moved')) as ok, sum(result = 'not_found') as nf")
                ->groupBy('locations.full_path')->orderBy('locations.full_path')->get(),
        ]);
    }

    public function disposal(Disposal $disposal)
    {
        abort_unless(PenghapusanAset::canAccess(), 403);

        return view('reports.berita-acara-penghapusan', [
            'd' => $disposal->load('asset.location', 'asset.category', 'requester', 'approver', 'completer'),
            'property' => app(CurrentProperty::class)->get(), 'fin' => auth()->user()->canSeeFinancials(),
        ]);
    }
}
