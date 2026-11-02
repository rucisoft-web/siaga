<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\RoomBlock;
use App\Models\RoomSurvey;
use App\Models\ServiceRequest;
use App\Models\WoCostLine;
use App\Models\WorkOrder;
use App\Support\Fmt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Angka & seri grafik untuk Dasbor pimpinan: (1) kondisi & risiko aset, (2) kinerja perbaikan, (3) biaya & dampak.
 * Bisa difilter per gedung / lokasi utama (mis. per rumah sakit atau per wilayah outlet).
 */
class LeadershipService
{
    public const GRADES = ['A' => 'A · Baik', 'B' => 'B · Layak', 'C' => 'C · Perlu perhatian', 'D' => 'D · Rusak sebagian', 'E' => 'E · Rusak berat', '?' => 'Belum dinilai'];

    protected array $parent = [];

    protected array $rootOf = [];

    /** @var Collection<int, Location> */
    protected Collection $roots;

    protected ?array $scope = null;

    public function __construct()
    {
        $locs = Location::get(['id', 'parent_id', 'name', 'location_type']);
        $this->parent = $locs->pluck('parent_id', 'id')->all();
        $this->roots = $locs->whereNull('parent_id')->sortBy('id')->values();
        foreach ($locs as $l) {
            $this->rootOf[$l->id] = $this->root($l->id);
        }
    }

    protected function root(int $id): int
    {
        $guard = 0;
        while (($p = $this->parent[$id] ?? null) && $guard++ < 12) {
            $id = $p;
        }

        return $id;
    }

    /** @return array<int, string> lokasi utama (gedung / RS / wilayah) yang punya isi */
    public function buildings(): array
    {
        $used = array_count_values(array_values($this->rootOf));

        return $this->roots->filter(fn ($r) => ($used[$r->id] ?? 0) > 1)->pluck('name', 'id')->all();
    }

    public function data(?int $building = null): array
    {
        $this->scope = $building ? array_keys(array_filter($this->rootOf, fn ($r) => $r === $building)) : null;
        $now = now();
        $from6 = $now->copy()->subMonthsNoOverflow(5)->startOfMonth();
        $from90 = $now->copy()->subDays(90);

        $assets = $this->q(Asset::query())->whereIn('lifecycle_status', ['draft', 'active'])
            ->leftJoin('asset_financials as f', 'f.asset_id', '=', 'assets.id')
            ->get(['assets.id', 'assets.name', 'assets.location_id', 'assets.category_id', 'assets.condition_grade', 'assets.operational_status', 'assets.lifecycle_status',
                'assets.verification_status', 'assets.criticality', 'f.acquisition_cost', 'f.replacement_estimate']);
        $risky = fn ($a) => in_array($a->condition_grade, ['D', 'E'], true) || in_array($a->operational_status, ['out_of_service', 'under_repair'], true);
        $cats = AssetCategory::pluck('name', 'id')->all();

        $wos = $this->q(WorkOrder::query())->where('received_at', '>=', $from6)
            ->get(['id', 'asset_id', 'location_id', 'type', 'status', 'priority', 'received_at', 'due_at', 'restored_at', 'completed_at', 'hold_minutes', 'created_at']);
        $open = $this->q(WorkOrder::query())->whereIn('status', WorkOrder::OPEN)->get(['id', 'status', 'priority', 'created_at', 'due_at', 'restored_at']);
        $costs = WoCostLine::query()->join('work_orders as w', 'w.id', '=', 'wo_cost_lines.work_order_id')
            ->when($this->scope !== null, fn ($q) => $q->whereIn('w.location_id', $this->scope ?: [0]))
            ->where('wo_cost_lines.created_at', '>=', $from6)
            ->get(['wo_cost_lines.amount', 'wo_cost_lines.type', 'wo_cost_lines.created_at', 'wo_cost_lines.is_cash_out', 'w.asset_id', 'w.id as wo_id']);

        return [
            'kpi' => $this->kpis($assets, $risky, $open, $wos, $costs, $from90),
            'condition_by_building' => $this->conditionBy($assets, fn ($a) => $this->rootName($a->location_id)),
            'condition_by_category' => $this->conditionBy($assets, fn ($a) => $cats[$a->category_id] ?? 'Lain-lain', 8),
            'risk_value_by_category' => $this->riskValue($assets, $risky, $cats),
            'survey' => $this->survey(),
            'weekly' => $this->weekly($wos),
            'backlog_status' => $this->backlogStatus($open),
            'backlog_age' => $this->backlogAge($open),
            'sla_priority' => $this->slaByPriority($wos->where('received_at', '>=', $from90)),
            'mttr_category' => $this->mttrByCategory($from90),
            'cost_monthly' => $this->costMonthly($costs, $from6),
            'cost_category' => $this->costByCategory($costs, $cats),
            'top_assets' => $this->topAssets($costs, $wos),
            'repeat' => $this->repeat($wos->where('received_at', '>=', $from90)),
            'room_days' => $this->roomDays($from6),
            'risk_list' => $assets->filter($risky)->sortByDesc(fn ($a) => [$a->criticality === 'high' ? 1 : 0, (float) $a->acquisition_cost])->take(8)
                ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'where' => Location::find($a->location_id)?->name, 'grade' => $a->condition_grade, 'status' => $a->operational_status, 'value' => (float) $a->acquisition_cost])->values()->all(),
        ];
    }

    protected function q($query)
    {
        return $this->scope === null ? $query : $query->whereIn($query->getModel()->getTable().'.location_id', $this->scope ?: [0]);
    }

    protected function rootName(?int $loc): string
    {
        $r = $loc ? ($this->rootOf[$loc] ?? null) : null;

        return $r ? ($this->roots->firstWhere('id', $r)?->name ?? '—') : '—';
    }

    protected function kpis(Collection $assets, \Closure $risky, Collection $open, Collection $wos, Collection $costs, Carbon $from90): array
    {
        $active = $assets->where('lifecycle_status', 'active');
        $restored = $wos->filter(fn ($w) => $w->restored_at && $w->received_at >= $from90 && in_array($w->type, ['corrective', 'emergency'], true));
        $withDue = $restored->filter(fn ($w) => $w->due_at);
        $met = $withDue->filter(fn ($w) => $w->restored_at <= $w->due_at)->count();
        $repeat = $wos->where('received_at', '>=', $from90)->whereNotNull('asset_id')->groupBy('asset_id')->filter(fn ($g) => $g->count() >= 2)->count();
        $d30 = now()->subDays(30);

        return [
            'assets' => $assets->count(),
            'active' => $active->count(),
            'verified_pct' => $assets->count() ? round($assets->where('verification_status', 'verified')->count() / $assets->count() * 100) : null,
            'value' => (float) $active->sum('acquisition_cost'),
            'risk_count' => $assets->filter($risky)->count(),
            'risk_value' => (float) $assets->filter($risky)->sum(fn ($a) => (float) ($a->replacement_estimate ?: $a->acquisition_cost)),
            'open_wo' => $open->count(),
            'overdue_wo' => $open->filter(fn ($w) => $w->due_at && ! $w->restored_at && $w->due_at->isPast())->count(),
            'sla_pct' => $withDue->count() ? round($met / $withDue->count() * 100) : null,
            'sla_note' => $met.' dari '.$withDue->count().' perbaikan',
            'mttr_h' => $restored->count() ? round($restored->avg(fn ($w) => $w->received_at->diffInMinutes($w->restored_at)) / 60, 1) : null,
            'cost_30' => (float) $costs->where('created_at', '>=', $d30)->sum('amount'),
            'cost_6m' => (float) $costs->sum('amount'),
            'repeat' => $repeat,
            'room_days_90' => $this->roomDaysTotal($from90),
            'requests_open' => $this->q(ServiceRequest::query())->whereIn('status', ServiceRequest::OPEN)->count(),
        ];
    }

    protected function conditionBy(Collection $assets, \Closure $key, int $limit = 12): array
    {
        $groups = $assets->groupBy($key)->sortByDesc(fn ($g) => $g->count())->take($limit);
        $series = [];
        foreach (array_keys(self::GRADES) as $g) {
            $series[$g] = $groups->map(fn ($list) => $list->filter(fn ($a) => ($a->condition_grade ?: '?') === $g)->count())->values()->all();
        }

        return ['labels' => $groups->keys()->values()->all(), 'series' => $series];
    }

    protected function riskValue(Collection $assets, \Closure $risky, array $cats): array
    {
        $g = $assets->filter($risky)->groupBy(fn ($a) => $cats[$a->category_id] ?? 'Lain-lain')
            ->map(fn ($l) => (float) $l->sum(fn ($a) => (float) ($a->replacement_estimate ?: $a->acquisition_cost)))->filter()->sortDesc()->take(8);

        return ['labels' => $g->keys()->all(), 'values' => $g->values()->all()];
    }

    protected function survey(): array
    {
        $rooms = Location::where('location_type', 'room')->get(['id']);
        $done = RoomSurvey::where('status', 'done')->pluck('location_id')->flip();
        $by = [];
        foreach ($rooms as $r) {
            if ($this->scope !== null && ! in_array($r->id, $this->scope, true)) {
                continue;
            }
            $b = $this->rootName($r->id);
            $by[$b] ??= ['total' => 0, 'done' => 0];
            $by[$b]['total']++;
            $by[$b]['done'] += isset($done[$r->id]) ? 1 : 0;
        }
        // kurva kumulatif ruang selesai didata per hari (30 hari)
        $days = [];
        $start = now()->subDays(29)->startOfDay();
        $fin = RoomSurvey::where('status', 'done')->whereNotNull('finished_at')->when($this->scope !== null, fn ($q) => $q->whereIn('location_id', $this->scope ?: [0]))->pluck('finished_at');
        for ($d = 0; $d < 30; $d++) {
            $end = $start->copy()->addDays($d + 1);
            $days['labels'][] = $start->copy()->addDays($d)->timezone(Fmt::tz())->format('d/m');
            $days['values'][] = $fin->filter(fn ($t) => Carbon::parse($t) < $end)->count();
        }

        return ['labels' => array_keys($by), 'done' => array_column($by, 'done'), 'todo' => array_map(fn ($x) => $x['total'] - $x['done'], array_values($by)), 'curve' => $days];
    }

    protected function weekly(Collection $wos): array
    {
        $labels = $in = $out = [];
        $start = now()->startOfWeek()->subWeeks(11);
        for ($i = 0; $i < 12; $i++) {
            $a = $start->copy()->addWeeks($i);
            $b = $a->copy()->addWeek();
            $labels[] = $a->timezone(Fmt::tz())->format('d M');
            $in[] = $wos->filter(fn ($w) => $w->received_at >= $a && $w->received_at < $b)->count();
            $out[] = $wos->filter(fn ($w) => $w->completed_at && $w->completed_at >= $a && $w->completed_at < $b)->count();
        }

        return ['labels' => $labels, 'in' => $in, 'out' => $out];
    }

    protected function backlogStatus(Collection $open): array
    {
        $names = ['draft' => 'Draf', 'awaiting_approval' => 'Menunggu persetujuan', 'approved' => 'Disetujui', 'assigned' => 'Ditugaskan', 'in_progress' => 'Dikerjakan',
            'on_hold' => 'Ditunda', 'completed' => 'Selesai teknisi', 'verified' => 'Terverifikasi', 'reopened' => 'Dibuka ulang'];
        $c = $open->countBy('status')->all();
        $keys = array_values(array_filter(array_keys($names), fn ($k) => isset($c[$k])));

        return ['labels' => array_map(fn ($k) => $names[$k], $keys), 'values' => array_map(fn ($k) => $c[$k], $keys)];
    }

    protected function backlogAge(Collection $open): array
    {
        $b = ['0–3 hari' => 0, '4–7 hari' => 0, '8–14 hari' => 0, '15–30 hari' => 0, '> 30 hari' => 0];
        foreach ($open as $w) {
            $d = $w->created_at->diffInDays(now());
            $b[$d <= 3 ? '0–3 hari' : ($d <= 7 ? '4–7 hari' : ($d <= 14 ? '8–14 hari' : ($d <= 30 ? '15–30 hari' : '> 30 hari')))]++;
        }

        return ['labels' => array_keys($b), 'values' => array_values($b)];
    }

    protected function slaByPriority(Collection $wos): array
    {
        $out = ['labels' => [], 'met' => [], 'missed' => []];
        foreach (['P1', 'P2', 'P3', 'P4'] as $p) {
            $l = $wos->where('priority', $p)->filter(fn ($w) => $w->restored_at && $w->due_at);
            $met = $l->filter(fn ($w) => $w->restored_at <= $w->due_at)->count();
            $out['labels'][] = $p;
            $out['met'][] = $met;
            $out['missed'][] = $l->count() - $met;
        }

        return $out;
    }

    protected function mttrByCategory(Carbon $from): array
    {
        $rows = $this->q(WorkOrder::query())->where('received_at', '>=', $from)->whereNotNull('restored_at')->with('requests:id,category')->get(['id', 'received_at', 'restored_at']);
        $g = $rows->groupBy(fn ($w) => ServiceRequest::CATEGORIES[$w->requests->first()?->category] ?? 'Preventif / lainnya')
            ->map(fn ($l) => round($l->avg(fn ($w) => $w->received_at->diffInMinutes($w->restored_at)) / 60, 1))->sortDesc()->take(8);

        return ['labels' => $g->keys()->all(), 'values' => $g->values()->all()];
    }

    protected function costMonthly(Collection $costs, Carbon $from): array
    {
        $types = ['material' => 'Material', 'vendor' => 'Jasa vendor', 'labor' => 'Tenaga internal', 'other' => 'Lain-lain'];
        $labels = $keys = [];
        for ($i = 0; $i < 6; $i++) {
            $m = $from->copy()->addMonthsNoOverflow($i);
            $keys[] = $m->format('Y-m');
            $labels[] = $m->translatedFormat('M Y');
        }
        $series = [];
        foreach ($types as $t => $name) {
            $series[$name] = array_map(fn ($k) => (float) $costs->filter(fn ($c) => $c->type === $t && Carbon::parse($c->created_at)->format('Y-m') === $k)->sum('amount'), $keys);
        }

        return ['labels' => $labels, 'series' => array_filter($series, fn ($v) => array_sum($v) > 0)];
    }

    protected function costByCategory(Collection $costs, array $cats): array
    {
        $assetCat = Asset::whereIn('id', $costs->pluck('asset_id')->filter()->unique())->pluck('category_id', 'id');
        $g = $costs->groupBy(fn ($c) => $cats[$assetCat[$c->asset_id] ?? 0] ?? 'Tanpa aset')->map(fn ($l) => (float) $l->sum('amount'))->sortDesc();
        $top = $g->take(7);
        if ($g->count() > 7) {
            $top['Lainnya'] = (float) $g->slice(7)->sum();
        }

        return ['labels' => $top->keys()->all(), 'values' => $top->values()->all()];
    }

    protected function topAssets(Collection $costs, Collection $wos): array
    {
        $by = $costs->whereNotNull('asset_id')->groupBy('asset_id')->map(fn ($l) => (float) $l->sum('amount'))->sortDesc()->take(10);
        $assets = Asset::with('location', 'financial')->whereIn('id', $by->keys())->get()->keyBy('id');

        return $by->map(function ($cost, $id) use ($assets, $wos) {
            $a = $assets[$id] ?? null;
            $acq = (float) ($a?->financial?->acquisition_cost ?? 0);

            return ['id' => $id, 'name' => $a?->name, 'code' => $a?->asset_code, 'where' => $a?->location?->name, 'cost' => $cost, 'count' => $wos->where('asset_id', $id)->count(),
                'acq' => $acq, 'ratio' => $acq > 0 ? round($cost / $acq * 100) : null];
        })->values()->all();
    }

    protected function repeat(Collection $wos): array
    {
        $g = $wos->whereNotNull('asset_id')->groupBy('asset_id')->filter(fn ($l) => $l->count() >= 2)->sortByDesc(fn ($l) => $l->count())->take(8);
        $assets = Asset::with('location')->whereIn('id', $g->keys())->get()->keyBy('id');

        return $g->map(fn ($l, $id) => ['id' => $id, 'name' => $assets[$id]?->name, 'where' => $assets[$id]?->location?->name, 'count' => $l->count(),
            'last' => Fmt::date($l->max('received_at'))])->values()->all();
    }

    protected function blocks(Carbon $from): Collection
    {
        return RoomBlock::whereIn('status', ['confirmed', 'released'])->where(fn ($q) => $q->whereNull('ended_at')->orWhere('ended_at', '>=', $from))
            ->when($this->scope !== null, fn ($q) => $q->whereIn('room_id', $this->scope ?: [0]))->get(['room_id', 'started_at', 'ended_at', 'fo_confirmed_at', 'created_at']);
    }

    protected function roomDaysTotal(Carbon $from): float
    {
        return round($this->blocks($from)->sum(fn ($b) => $this->overlapDays($b, $from, now())), 1);
    }

    protected function roomDays(Carbon $from): array
    {
        $blocks = $this->blocks($from);
        $labels = $values = [];
        for ($i = 0; $i < 6; $i++) {
            $a = $from->copy()->addMonthsNoOverflow($i);
            $b = $a->copy()->addMonthNoOverflow();
            $labels[] = $a->translatedFormat('M Y');
            $values[] = round($blocks->sum(fn ($x) => $this->overlapDays($x, $a, $b->min(now()))), 1);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function overlapDays($b, Carbon $from, Carbon $to): float
    {
        $s = Carbon::parse($b->started_at ?? $b->fo_confirmed_at ?? $b->created_at);
        $e = $b->ended_at ? Carbon::parse($b->ended_at) : now();
        $s = $s->max($from);
        $e = $e->min($to);

        return $e > $s ? $s->diffInMinutes($e) / 1440 : 0;
    }
}
