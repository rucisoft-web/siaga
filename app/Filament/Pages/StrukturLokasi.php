<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Asset;
use App\Models\Location;
use App\Models\WorkOrder;
use App\Support\CurrentProperty;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

/** Struktur lokasi aset berbentuk pohon (gedung › lantai › kamar/ruang) dengan jumlah aset, kondisi, dan WO terbuka. */
class StrukturLokasi extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Aset';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Struktur lokasi';

    protected static ?string $title = 'Struktur lokasi aset';

    protected static ?string $slug = 'struktur-lokasi';

    protected string $view = 'filament.pages.struktur-lokasi';

    #[Url(as: 'pilih')]
    public ?int $pilih = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPermission('asset.view');
    }

    protected function getViewData(): array
    {
        $pid = app(CurrentProperty::class)->id();
        $financial = auth()->user()->canSeeFinancials();
        $locs = Location::query()->where('is_active', true)->orderBy('sort_key')->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'code', 'location_type', 'room_ref', 'full_path', 'pms_room_status']);

        // agregat aset per lokasi langsung
        $rows = Asset::query()->where('lifecycle_status', '!=', 'disposed')->whereNotNull('location_id')
            ->select('location_id', DB::raw('count(*) as n'),
                DB::raw("sum(condition_grade in ('D','E')) as risk"),
                DB::raw('sum(condition_grade is null) as unrated'))
            ->groupBy('location_id')->get()->keyBy('location_id');
        $value = $financial ? DB::table('assets')->join('asset_financials', 'asset_financials.asset_id', '=', 'assets.id')
            ->where('assets.property_id', $pid)->whereNotNull('assets.location_id')
            ->groupBy('assets.location_id')->selectRaw('assets.location_id as loc, sum(asset_financials.acquisition_cost) as v')->pluck('v', 'loc') : collect();
        $wo = WorkOrder::query()->whereIn('status', WorkOrder::OPEN)->whereNotNull('location_id')
            ->groupBy('location_id')->selectRaw('location_id, count(*) as c')->pluck('c', 'location_id');

        $nodes = [];
        foreach ($locs as $l) {
            $r = $rows[$l->id] ?? null;
            $nodes[$l->id] = [
                'id' => $l->id, 'parent' => $l->parent_id, 'name' => $l->name, 'code' => $l->code, 'type' => $l->location_type,
                'path' => $l->full_path ?: $l->name, 'pms' => $l->pms_room_status,
                'own' => (int) ($r->n ?? 0), 'n' => (int) ($r->n ?? 0), 'risk' => (int) ($r->risk ?? 0),
                'unrated' => (int) ($r->unrated ?? 0), 'wo' => (int) ($wo[$l->id] ?? 0), 'value' => (float) ($value[$l->id] ?? 0),
                'children' => [],
            ];
        }
        $roots = [];
        foreach ($nodes as $id => $n) {
            if ($n['parent'] && isset($nodes[$n['parent']])) {
                $nodes[$n['parent']]['children'][] = $id;
            } else {
                $roots[] = $id;
            }
        }
        // jumlahkan ke atas (pasca-urut)
        $sum = function (int $id) use (&$sum, &$nodes) {
            foreach ($nodes[$id]['children'] as $c) {
                $sum($c);
                foreach (['n', 'risk', 'unrated', 'wo', 'value'] as $k) {
                    $nodes[$id][$k] += $nodes[$c][$k];
                }
            }
        };
        foreach ($roots as $r) {
            $sum($r);
        }

        $sel = $this->pilih && isset($nodes[$this->pilih]) ? $nodes[$this->pilih] : null;
        $selAssets = collect();
        $crumbs = [];
        if ($sel) {
            $ids = [];
            $walk = function (int $id) use (&$walk, &$ids, $nodes) {
                $ids[] = $id;
                foreach ($nodes[$id]['children'] as $c) {
                    $walk($c);
                }
            };
            $walk($sel['id']);
            $selAssets = Asset::query()->with(['category:id,name', 'location:id,name'])->whereIn('location_id', $ids)
                ->where('lifecycle_status', '!=', 'disposed')
                ->orderByRaw("case when condition_grade in ('D','E') then 0 when condition_grade = 'C' then 1 else 2 end")
                ->orderBy('asset_code')->limit(30)->get();
            for ($p = $sel; $p; $p = $p['parent'] ? ($nodes[$p['parent']] ?? null) : null) {
                array_unshift($crumbs, $p);
            }
        }

        return [
            'nodes' => $nodes, 'roots' => $roots, 'sel' => $sel, 'selAssets' => $selAssets, 'crumbs' => $crumbs,
            'financial' => $financial, 'types' => Location::TYPES,
            'total' => ['loc' => count($nodes), 'n' => array_sum(array_map(fn ($r) => $nodes[$r]['n'], $roots)),
                'risk' => array_sum(array_map(fn ($r) => $nodes[$r]['risk'], $roots)), 'wo' => array_sum(array_map(fn ($r) => $nodes[$r]['wo'], $roots))],
            'assetsUrl' => fn (int $loc) => AssetResource::getUrl('index').'?'.http_build_query(['filters' => ['location_id' => ['value' => $loc]]]),
            'assetUrl' => fn (Asset $a) => AssetResource::getUrl('view', ['record' => $a]),
        ];
    }
}
