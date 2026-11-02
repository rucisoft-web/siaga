<?php

namespace App\Filament\Pages;

use App\Models\AssetCategory;
use App\Models\Location;
use App\Services\DepreciationService;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/** Penyusutan & nilai buku aset tetap (PSAK 16): ringkasan per kategori, jurnal bulanan, dan daftar aset tetap. */
class Penyusutan extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Penyusutan & nilai buku';

    protected static ?string $title = 'Penyusutan & nilai buku aset tetap';

    protected static ?string $slug = 'penyusutan';

    protected string $view = 'filament.pages.penyusutan';

    #[Url]
    public ?string $periode = null;

    #[Url]
    public ?int $kategori = null;

    #[Url]
    public ?int $lokasi = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPermission('asset.view_financial');
    }

    public static function asOf(?string $periode): CarbonImmutable
    {
        $p = $periode && preg_match('/^\d{4}-\d{2}$/', $periode) ? CarbonImmutable::createFromFormat('!Y-m', $periode) : CarbonImmutable::now()->subMonthNoOverflow();

        return $p->endOfMonth()->startOfDay();
    }

    public static function locationScope(?int $root): ?array
    {
        if (! $root) {
            return null;
        }
        $all = Location::query()->get(['id', 'parent_id'])->groupBy('parent_id');
        $ids = [];
        $walk = function (int $id) use (&$walk, &$ids, $all) {
            $ids[] = $id;
            foreach ($all[$id] ?? [] as $c) {
                $walk($c->id);
            }
        };
        $walk($root);

        return $ids;
    }

    protected function getViewData(): array
    {
        $asOf = static::asOf($this->periode);
        $rows = (new DepreciationService)->register($asOf, $this->kategori, static::locationScope($this->lokasi));
        $sum = fn (string $k, $set = null) => ($set ?? $rows)->sum(fn ($r) => $r['d'][$k]);
        $byCat = $rows->groupBy(fn ($r) => $r['asset']->category_id)->map(function ($g) use ($sum) {
            $c = $g->first()['asset']->category;

            return ['cat' => $c, 'n' => $g->count(), 'cost' => $sum('cost', $g), 'accum' => $sum('accum', $g), 'book' => $sum('book', $g),
                'period' => $sum('period', $g), 'ytd' => $sum('ytd', $g), 'fully' => $g->where('d.fully', true)->count()];
        })->sortByDesc('book')->values();

        $months = collect(range(0, 23))->map(fn ($i) => CarbonImmutable::now()->subMonthsNoOverflow($i + 1))->mapWithKeys(fn ($m) => [$m->format('Y-m') => $m->translatedFormat('F Y')]);

        return [
            'asOf' => $asOf, 'rows' => $rows, 'byCat' => $byCat,
            'top' => $rows->sortByDesc(fn ($r) => $r['d']['book'])->take(50)->values(),
            'kpi' => ['n' => $rows->count(), 'cost' => $sum('cost'), 'accum' => $sum('accum'), 'book' => $sum('book'), 'period' => $sum('period'),
                'ytd' => $sum('ytd'), 'fully' => $rows->where('d.fully', true)->count(),
                'fullyCost' => $rows->where('d.fully', true)->sum(fn ($r) => $r['d']['cost'])],
            'months' => $months, 'periodeKey' => $asOf->format('Y-m'),
            'categories' => AssetCategory::query()->orderBy('name')->pluck('name', 'id'),
            'roots' => Location::query()->whereNull('parent_id')->orderBy('sort_key')->pluck('name', 'id'),
            'methods' => DepreciationService::METHODS,
            'exportUrl' => route('reports.depreciation', array_filter(['periode' => $asOf->format('Y-m'), 'kategori' => $this->kategori, 'lokasi' => $this->lokasi])),
            'canExport' => auth()->user()->hasPermission('report.export'),
        ];
    }
}
