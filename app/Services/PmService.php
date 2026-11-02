<?php

namespace App\Services;

use App\Filament\Pages\JadwalPm;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\Asset;
use App\Models\PmSchedule;
use App\Models\Property;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Support\CurrentProperty;
use App\Support\Fmt;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Jadwal PM / inspeksi / kalibrasi → work order otomatis; pengingat garansi & kontrak vendor. */
class PmService
{
    public function __construct(protected NotificationService $notify) {}

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now(Fmt::tz())->startOfDay();
    }

    /** Aset sasaran jadwal (satu aset, atau kategori di lokasi tertentu beserta sub-lokasinya). */
    public function targetAssets(PmSchedule $s): Collection
    {
        if ($s->asset_id) {
            return Asset::withoutGlobalScopes()->whereKey($s->asset_id)->get();
        }
        $ids = $s->location_id ? app(StockTakeService::class)->scopeIds($s->location_id) : null;

        return Asset::withoutGlobalScopes()->where('property_id', $s->property_id)->whereIn('lifecycle_status', ['draft', 'active'])
            ->when($s->category_id, fn ($q) => $q->where('category_id', $s->category_id))
            ->when($ids !== null, fn ($q) => $q->whereIn('location_id', $ids ?: [0]))
            ->orderBy('asset_code')->get();
    }

    /** Buat WO untuk jatuh tempo berikutnya. Null bila WO untuk tanggal itu sudah ada. */
    public function generate(PmSchedule $s, ?User $by = null): ?WorkOrder
    {
        $today = self::today();

        return DB::transaction(function () use ($s, $by, $today) {
            $s = PmSchedule::withoutGlobalScopes()->whereKey($s->id)->lockForUpdate()->first();
            $due = CarbonImmutable::parse($s->next_due->toDateString(), Fmt::tz());
            if (WorkOrder::withoutGlobalScopes()->where('pm_schedule_id', $s->id)->whereDate('pm_due_date', $due->toDateString())->exists()) {
                return null;
            }
            $assets = $this->targetAssets($s);
            $single = $s->asset_id ? $assets->first() : ($assets->count() === 1 ? $assets->first() : null);
            $lines = array_filter([
                PmSchedule::TYPES[$s->type].' '.$s->intervalLabel().' · jadwal '.$s->code.' · jatuh tempo '.$due->translatedFormat('d M Y'),
                'Sasaran: '.$s->targetLabel().($single ? '' : ' ('.$assets->count().' unit)'),
                $s->regulation ? 'Dasar: '.$s->regulation : null,
                $s->vendor ? 'Vendor: '.$s->vendor->name : null,
                $s->pic ? 'PIC usulan: '.$s->pic->name : null,
                $s->checklist ? "\nDaftar periksa:\n".collect(preg_split('/\R/', trim($s->checklist)))->filter()->map(fn ($l) => '☐ '.trim($l))->join("\n") : null,
                ! $single && $assets->isNotEmpty() ? "\nAset:\n".$assets->take(60)->map(fn ($a) => '☐ '.$a->asset_code.' '.$a->name)->join("\n").($assets->count() > 60 ? "\n… dan ".($assets->count() - 60).' lainnya' : '') : null,
            ]);
            $wo = WorkOrder::create([
                'property_id' => $s->property_id,
                'type' => $s->type, 'pm_schedule_id' => $s->id, 'pm_due_date' => $due->toDateString(),
                'title' => $s->title, 'description' => implode("\n", $lines),
                'asset_id' => $single?->id, 'location_id' => $single?->location_id ?? $s->location_id,
                'priority' => $s->priority, 'status' => 'draft', 'received_at' => now(),
                'scheduled_at' => $due->setTime(8, 0)->utc(), 'due_at' => $due->endOfDay()->utc(), 'sla_calendar' => '24h',
                'estimated_hours' => $s->estimated_hours, 'estimated_cost' => 0,
                'needs_vendor' => (bool) $s->vendor_id, 'vendor_id' => $s->vendor_id, 'created_by' => $by?->id,
            ]);
            app(WorkOrderService::class)->event($wo, null, 'draft', 'create', 'Dibuat '.($by ? 'manual' : 'otomatis').' dari jadwal '.$s->code);

            $next = $due->addDays($s->interval_days);
            while ($next->lessThanOrEqualTo($today)) {
                $next = $next->addDays($s->interval_days);
            }
            $s->update(['next_due' => $next->toDateString(), 'last_work_order_id' => $wo->id, 'last_generated_at' => now()]);

            $to = $this->recipients($s->property_id, 'wo.assign')->merge(collect([$s->pic])->filter())->unique('id');
            DB::afterCommit(fn () => $this->notify->send($to, PmSchedule::TYPES[$s->type].': '.$s->title,
                $wo->number.' · jatuh tempo '.$due->translatedFormat('d M Y').' · '.$s->targetLabel(),
                WorkOrderResource::getUrl('view', ['record' => $wo], panel: 'admin'), 'pm-'.$s->id.'-'.$due->toDateString(), 'info'));

            return $wo;
        });
    }

    /** Jalankan semua jadwal yang sudah masuk masa tenggang (jatuh tempo − lead_days ≤ hari ini). */
    public function runDue(?int $propertyId = null): array
    {
        $today = self::today();
        $made = [];
        $cp = app(CurrentProperty::class);
        $orig = $cp->get();
        try {
            $q = PmSchedule::withoutGlobalScopes()->where('is_active', true)->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))->orderBy('next_due');
            foreach ($q->get() as $s) {
                if ($s->next_due->copy()->subDays($s->lead_days)->toDateString() > $today->toDateString()) {
                    continue;
                }
                $cp->set(Property::find($s->property_id));
                if ($wo = $this->generate($s)) {
                    $made[] = $wo->number;
                }
            }
        } finally {
            $cp->set($orig);
        }

        return $made;
    }

    /** Garansi aset dan kontrak vendor yang berakhir dalam $days hari (termasuk yang sudah lewat 30 hari terakhir). */
    public function expiring(int $days = 60, ?int $propertyId = null): Collection
    {
        $today = self::today();
        $to = $today->addDays($days)->toDateString();
        $from = $today->subDays(30)->toDateString();
        $pid = $propertyId ?? app(CurrentProperty::class)->id();
        $assets = Asset::withoutGlobalScopes()->where('property_id', $pid)->whereIn('lifecycle_status', ['draft', 'active'])
            ->whereBetween('warranty_until', [$from, $to])->with('location:id,name,full_path')->get(['id', 'asset_code', 'name', 'location_id', 'warranty_until', 'vendor_id', 'contract_ref'])
            ->map(fn ($a) => ['kind' => 'Garansi aset', 'id' => $a->id, 'ref' => $a->asset_code, 'name' => $a->name, 'where' => $a->location?->name, 'until' => CarbonImmutable::parse($a->warranty_until), 'model' => $a]);
        $vendors = Vendor::withoutGlobalScopes()->where('property_id', $pid)->where('is_active', true)->whereBetween('contract_until', [$from, $to])->get()
            ->map(fn ($v) => ['kind' => 'Kontrak vendor', 'id' => $v->id, 'ref' => $v->contract_ref ?: $v->code, 'name' => $v->name, 'where' => $v->specialization, 'until' => CarbonImmutable::parse($v->contract_until), 'model' => $v]);

        return $assets->concat($vendors)->map(fn ($r) => $r + ['days' => (int) $today->diffInDays($r['until'], false)])->sortBy('days')->values();
    }

    /** Kirim pengingat H-30 dan H-7 (sekali per ambang) untuk garansi & kontrak. */
    public function remind(): int
    {
        $sent = 0;
        foreach (Property::where('is_active', true)->get() as $p) {
            $to = $this->recipients($p->id, 'pm.manage');
            if ($to->isEmpty()) {
                continue;
            }
            foreach ($this->expiring(30, $p->id) as $r) {
                if ($r['days'] < 0) {
                    continue;
                }
                $stage = $r['days'] <= 7 ? 7 : 30;
                $url = $r['kind'] === 'Garansi aset'
                    ? \App\Filament\Resources\Assets\AssetResource::getUrl('view', ['record' => $r['id']], panel: 'admin')
                    : JadwalPm::getUrl(panel: 'admin');
                $sent += (int) $this->notify->send($to, $r['kind'].' berakhir '.($r['days'] === 0 ? 'hari ini' : 'dalam '.$r['days'].' hari'),
                    $r['ref'].' '.$r['name'].' · s.d. '.$r['until']->translatedFormat('d M Y').'. Ajukan klaim / perpanjangan sebelum berakhir.',
                    $url, 'exp-'.$r['kind'].'-'.$r['id'].'-'.$r['until']->toDateString().'-'.$stage, $stage === 7 ? 'warning' : 'info');
            }
        }

        return $sent;
    }

    /** Pengguna aktif perusahaan itu yang memegang izin tertentu (peran global tidak ikut agar tidak dibanjiri). */
    protected function recipients(int $propertyId, string $perm): Collection
    {
        return User::withoutGlobalScopes()->where('is_active', true)->where('property_id', $propertyId)->with('roles.permissions')->get()
            ->filter(fn (User $u) => in_array($perm, $u->permissionSlugs($propertyId), true) || $u->isSuperAdmin())->values();
    }
}
