<?php

namespace App\Services;

use App\Models\Disposal;
use App\Models\Property;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Support\CurrentProperty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

/** Konsolidasi grup: ringkasan KPI setiap perusahaan aktif dalam satu instalasi. */
class GroupService
{
    /** @return array<int, array> */
    public function companies(bool $fresh = false): array
    {
        $cp = app(CurrentProperty::class);
        $orig = $cp->get();
        $out = [];
        try {
            foreach (Property::where('is_active', true)->orderBy('name')->get() as $p) {
                $key = 'group-kpi-'.$p->id;
                if ($fresh) {
                    Cache::forget($key);
                }
                $out[] = Cache::remember($key, 600, function () use ($p, $cp) {
                    $cp->set($p);
                    $k = (new LeadershipService)->data()['kpi'];
                    $dep = (new DepreciationService)->register(CarbonImmutable::now()->subMonthNoOverflow()->endOfMonth());
                    $lastSo = StockTake::where('status', 'closed')->latest('closed_at')->first();
                    $soAcc = $lastSo ? (function () use ($lastSo) {
                        $c = StockTakeItem::where('stock_take_id', $lastSo->id)->selectRaw("sum(result='found') f, sum(result in ('found','moved','not_found')) d")->first();

                        return $c->d ? round($c->f / $c->d * 100) : null;
                    })() : null;
                    $pm = class_exists(\App\Models\PmSchedule::class) ? \App\Models\PmSchedule::where('is_active', true)->whereDate('next_due', '<', now())->count() : null;

                    return [
                        'id' => $p->id, 'code' => $p->code, 'name' => $p->name, 'city' => $p->city,
                        'assets' => $k['assets'], 'risk' => $k['risk_count'], 'risk_pct' => $k['assets'] ? round($k['risk_count'] / $k['assets'] * 100, 1) : 0,
                        'verified_pct' => $k['verified_pct'], 'value' => $k['value'],
                        'cost_basis' => (float) $dep->sum(fn ($r) => $r['d']['cost']), 'book' => (float) $dep->sum(fn ($r) => $r['d']['book']),
                        'dep_month' => (float) $dep->sum(fn ($r) => $r['d']['period']),
                        'open_wo' => $k['open_wo'], 'overdue_wo' => $k['overdue_wo'], 'sla_pct' => $k['sla_pct'], 'mttr_h' => $k['mttr_h'],
                        'cost_6m' => $k['cost_6m'], 'cost_30' => $k['cost_30'], 'room_days' => $k['room_days_90'],
                        'so_date' => $lastSo?->closed_at?->toDateString(), 'so_acc' => $soAcc,
                        'disposal_pending' => Disposal::whereIn('status', ['submitted', 'approved'])->count(),
                        'pm_overdue' => $pm,
                    ];
                });
            }
        } finally {
            $cp->set($orig);
        }

        return $out;
    }
}
