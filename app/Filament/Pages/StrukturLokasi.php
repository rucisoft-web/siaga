<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Asset;
use App\Models\AssetCategory;
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

    #[Url]
    public ?int $kategori = null;

    #[Url]
    public ?string $jenis = null;

    #[Url]
    public string $tampilan = 'pohon';

    #[Url]
    public string $baris = 'lantai';

    /** "Nakas Unit 2" → "Nakas"; "Lampu baca/tidur Unit 1" → "Lampu baca/tidur" */
    public static function typeName(string $name): string
    {
        return trim(preg_replace('/\s+(unit|no\.?|#)\s*\d+$/i', '', $name));
    }

    /** Terapkan filter kategori / jenis barang ke query aset. */
    protected function filterAssets($q)
    {
        return $q->when($this->kategori, fn ($q) => $q->where('category_id', $this->kategori))
            ->when(filled($this->jenis), fn ($q) => $q->where(fn ($w) => $w->where('name', $this->jenis)
                ->orWhere('name', 'like', addcslashes($this->jenis, '%_\\').' Unit %')
                ->orWhere('name', 'like', addcslashes($this->jenis, '%_\\').' No%')));
    }

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
        $filtered = $this->kategori || filled($this->jenis);
        $rows = $this->filterAssets(Asset::query())->whereNotIn('lifecycle_status', ['disposed', 'lost'])->whereNotNull('location_id')
            ->select('location_id', DB::raw('count(*) as n'),
                DB::raw("sum(condition_grade in ('D','E')) as risk"),
                DB::raw('sum(condition_grade is null) as unrated'))
            ->groupBy('location_id')->get()->keyBy('location_id');
        $value = $financial ? $this->filterAssets(DB::table('assets'))->join('asset_financials', 'asset_financials.asset_id', '=', 'assets.id')
            ->where('assets.property_id', $pid)->whereNotNull('assets.location_id')->whereNotIn('assets.lifecycle_status', ['disposed', 'lost'])
            ->groupBy('assets.location_id')->selectRaw('assets.location_id as loc, sum(asset_financials.acquisition_cost) as v')->pluck('v', 'loc') : collect();
        $wo = WorkOrder::query()->whereIn('status', WorkOrder::OPEN)->whereNotNull('location_id')
            ->when($this->kategori || filled($this->jenis), fn ($q) => $q->whereIn('asset_id', $this->filterAssets(Asset::query())->select('id')))
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

        if ($filtered) {
            $keep = fn (int $id) => $nodes[$id]['n'] > 0;
            foreach ($nodes as $id => $n) {
                $nodes[$id]['children'] = array_values(array_filter($n['children'], $keep));
            }
            $roots = array_values(array_filter($roots, $keep));
        }
        $sel = $this->pilih && isset($nodes[$this->pilih]) ? $nodes[$this->pilih] : null;
        $selAssets = collect();
        $selTypes = collect();
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
            // jenis barang di lokasi ini (termasuk sub-lokasi)
            $selTypes = Asset::query()->whereIn('location_id', $ids)->whereNotIn('lifecycle_status', ['disposed', 'lost'])
                ->with('category:id,name')->select('name', 'category_id', DB::raw('count(*) as n'), DB::raw("sum(condition_grade in ('D','E')) as risk"))
                ->groupBy('name', 'category_id')->get()
                ->groupBy(fn ($r) => $r->category?->name ?? 'Tanpa kategori')
                ->map(fn ($g) => $g->groupBy(fn ($r) => static::typeName($r->name))->map(fn ($x) => ['n' => $x->sum('n'), 'risk' => (int) $x->sum('risk')])->sortByDesc('n'))
                ->sortByDesc(fn ($g) => $g->sum('n'));
            $selAssets = $this->filterAssets(Asset::query())->with(['category:id,name', 'location:id,name'])->whereIn('location_id', $ids)
                ->whereNotIn('lifecycle_status', ['disposed', 'lost'])
                ->orderByRaw("case when condition_grade in ('D','E') then 0 when condition_grade = 'C' then 1 else 2 end")
                ->orderBy('asset_code')->limit(30)->get();
            for ($p = $sel; $p; $p = $p['parent'] ? ($nodes[$p['parent']] ?? null) : null) {
                array_unshift($crumbs, $p);
            }
        }

        $matrix = $this->tampilan === 'matriks' ? $this->matrix($nodes, $roots) : null;

        return [
            'matrix' => $matrix,
            'nodes' => $nodes, 'roots' => $roots, 'sel' => $sel, 'selAssets' => $selAssets, 'crumbs' => $crumbs, 'selTypes' => $selTypes,
            'filtered' => $filtered, 'categories' => AssetCategory::query()->orderBy('name')->pluck('name', 'id'),
            'typeOptions' => Asset::query()->whereNotIn('lifecycle_status', ['disposed', 'lost'])->when($this->kategori, fn ($q) => $q->where('category_id', $this->kategori))
                ->distinct()->pluck('name')->map(fn ($n) => static::typeName($n))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'whereList' => $filtered ? collect($nodes)->filter(fn ($n) => $n['own'] > 0)->sortByDesc('own')->take(12)->values() : collect(),
            'financial' => $financial, 'types' => Location::typeLabels(),
            'total' => ['loc' => count($nodes), 'n' => array_sum(array_map(fn ($r) => $nodes[$r]['n'], $roots)),
                'risk' => array_sum(array_map(fn ($r) => $nodes[$r]['risk'], $roots)), 'wo' => array_sum(array_map(fn ($r) => $nodes[$r]['wo'], $roots))],
            'assetsUrl' => fn (int $loc) => AssetResource::getUrl('index').'?'.http_build_query(array_filter(['filters' => array_filter(['location_id' => ['value' => $loc], 'category_id' => $this->kategori ? ['value' => $this->kategori] : null]), 'search' => $this->jenis ?: null])),
            'link' => fn (array $p) => '?'.http_build_query(array_filter($p + ['pilih' => $this->pilih, 'kategori' => $this->kategori, 'jenis' => $this->jenis], fn ($v) => $v !== null && $v !== '')),
            'assetUrl' => fn (Asset $a) => AssetResource::getUrl('view', ['record' => $a]),
        ];
    }

    /**
     * Matriks lokasi × jenis barang. Baris = lokasi pada tingkat terpilih (gedung / lantai / ruang),
     * kolom = kategori (atau jenis barang bila kategori dipilih); sel = jumlah unit termasuk sub-lokasi.
     */
    protected function matrix(array $nodes, array $roots): array
    {
        // grup dengan beberapa badan usaha: PT berada di tingkat teratas, gedung satu tingkat di bawahnya
        $off = collect($roots)->contains(fn ($r) => ($nodes[$r]['type'] ?? null) === 'company') ? 1 : 0;
        $depthWanted = ['pt' => 0, 'gedung' => $off, 'lantai' => $off + 1, 'ruang' => 9][$this->baris] ?? $off + 1;
        $rowOf = [];
        $rows = [];
        $walk = function (int $id, int $depth, ?int $row) use (&$walk, &$rowOf, &$rows, $nodes, $depthWanted) {
            $leaf = empty($nodes[$id]['children']);
            if ($row === null && ($depth === $depthWanted || ($depthWanted === 9 && $leaf) || ($depth < $depthWanted && $leaf))) {
                $row = $id;
                $rows[] = $id;
            }
            $rowOf[$id] = $row;
            foreach ($nodes[$id]['children'] as $c) {
                $walk($c, $depth + 1, $row);
            }
        };
        foreach ($roots as $r) {
            $walk($r, 0, null);
        }
        $byType = (bool) $this->kategori;
        $q = $this->filterAssets(Asset::query())->whereNotIn('lifecycle_status', ['disposed', 'lost'])->whereNotNull('location_id');
        $data = $byType
            ? $q->select('location_id', 'name', DB::raw('count(*) as n'))->groupBy('location_id', 'name')->get()->map(fn ($r) => [$r->location_id, static::typeName($r->name), (int) $r->n])
            : $q->select('location_id', 'category_id', DB::raw('count(*) as n'))->groupBy('location_id', 'category_id')->get()->map(fn ($r) => [$r->location_id, (int) $r->category_id, (int) $r->n]);
        $cells = [];
        $colTotal = [];
        foreach ($data as [$loc, $col, $n]) {
            $row = $rowOf[$loc] ?? null;
            if ($row === null) {
                continue;
            }
            $cells[$row][$col] = ($cells[$row][$col] ?? 0) + $n;
            $colTotal[$col] = ($colTotal[$col] ?? 0) + $n;
        }
        arsort($colTotal);
        $cols = array_slice(array_keys($colTotal), 0, 40);
        $names = $byType ? array_combine($cols, $cols) : AssetCategory::query()->whereIn('id', $cols)->pluck('name', 'id')->all();
        $rows = array_values(array_filter($rows, fn ($r) => ! empty($cells[$r])));
        $max = 1;
        foreach ($rows as $r) {
            $max = max($max, ...array_values(array_intersect_key($cells[$r], array_flip($cols))) ?: [1]);
        }

        return ['rows' => $rows, 'cols' => $cols, 'names' => $names, 'cells' => $cells, 'colTotal' => $colTotal, 'max' => $max,
            'byType' => $byType, 'more' => max(0, count($colTotal) - count($cols))];
    }
}
