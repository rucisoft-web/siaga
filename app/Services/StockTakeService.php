<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Location;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Stock opname: snapshot daftar aset per cakupan lokasi saat sesi dibuka, petugas memindai / menandai,
 * lalu sesi ditutup: sisa "belum dihitung" menjadi "tidak ditemukan", dan (opsional) perbaikan lokasi & kondisi
 * diterapkan ke register. Aset tidak ditemukan ditindaklanjuti lewat usulan penghapusan.
 */
class StockTakeService
{
    public function __construct(protected QrResolver $resolver) {}

    /** @return int[] id lokasi dalam cakupan (lokasi + seluruh turunannya), null = semua */
    public function scopeIds(?int $root): ?array
    {
        if (! $root) {
            return null;
        }
        $children = Location::query()->get(['id', 'parent_id'])->groupBy('parent_id');
        $ids = [];
        $walk = function (int $id) use (&$walk, &$ids, $children) {
            $ids[] = $id;
            foreach ($children[$id] ?? [] as $c) {
                $walk($c->id);
            }
        };
        $walk($root);

        return $ids;
    }

    public function open(array $d, User $user): StockTake
    {
        if (! $user->hasPermission('stocktake.manage')) {
            throw WorkflowException::forbidden();
        }

        return DB::transaction(function () use ($d, $user) {
            $take = StockTake::create([
                'title' => $d['title'], 'location_id' => $d['location_id'] ?: null, 'cutoff_date' => $d['cutoff_date'] ?? now()->toDateString(),
                'notes' => $d['notes'] ?? null, 'status' => 'counting', 'created_by' => $user->id,
            ]);
            $ids = $this->scopeIds($take->location_id);
            $now = now();
            Asset::query()->whereNotIn('lifecycle_status', ['disposed', 'lost'])
                ->when($ids !== null, fn ($q) => $q->whereIn('location_id', $ids))
                ->select(['id', 'location_id', 'condition_grade'])->orderBy('id')
                ->chunk(1000, function ($chunk) use ($take, $now) {
                    StockTakeItem::insert($chunk->map(fn ($a) => [
                        'stock_take_id' => $take->id, 'asset_id' => $a->id, 'expected_location_id' => $a->location_id,
                        'expected_grade' => $a->condition_grade, 'result' => 'pending', 'created_at' => $now, 'updated_at' => $now,
                    ])->all());
                });

            return $take;
        });
    }

    protected function guard(StockTake $take, User $user): void
    {
        if (! $user->hasPermission('stocktake.count')) {
            throw WorkflowException::forbidden();
        }
        if ($take->status !== 'counting') {
            throw WorkflowException::invalid('Sesi stock opname sudah ditutup.');
        }
    }

    /**
     * Catat hasil pindai/ketik kode aset di lokasi tertentu.
     *
     * @return array{ok: bool, message: string, item?: StockTakeItem}
     */
    public function scan(StockTake $take, string $input, ?int $locationId, ?string $grade, User $user): array
    {
        $this->guard($take, $user);
        $r = $this->resolver->resolve($input);
        if (($r['type'] ?? '') !== 'asset' || empty($r['asset'])) {
            return ['ok' => false, 'message' => $r['message'] ?? 'Bukan label aset. Pindai label QR aset atau ketik kode aset.'];
        }
        $asset = $r['asset'];
        $item = $take->items()->where('asset_id', $asset->id)->first();
        if (! $item) {
            if ($asset->property_id !== $take->property_id) {
                return ['ok' => false, 'message' => 'Aset milik perusahaan lain.'];
            }
            // aset terdaftar tetapi di luar cakupan sesi: tetap dicatat sebagai temuan pindah lokasi
            $item = $take->items()->create(['asset_id' => $asset->id, 'expected_location_id' => $asset->location_id, 'expected_grade' => $asset->condition_grade, 'result' => 'pending']);
        }
        $this->mark($take, $item, $locationId, $grade, null, $user);

        return ['ok' => true, 'item' => $item->fresh(['asset', 'foundLocation']),
            'message' => $asset->asset_code.' · '.$asset->name.': '.StockTakeItem::RESULTS[$item->fresh()->result]];
    }

    public function mark(StockTake $take, StockTakeItem $item, ?int $locationId, ?string $grade, ?string $note, User $user, bool $notFound = false): StockTakeItem
    {
        $this->guard($take, $user);
        abort_unless($item->stock_take_id === $take->id, 404);
        $loc = $locationId ?: $item->expected_location_id;
        $item->update([
            'result' => $notFound ? 'not_found' : ($loc && $item->expected_location_id && $loc !== $item->expected_location_id ? 'moved' : 'found'),
            'found_location_id' => $notFound ? null : $loc,
            'found_grade' => $notFound ? null : ($grade && isset(Asset::CONDITIONS[$grade]) ? $grade : $item->found_grade),
            'note' => $note ?? $item->note, 'counted_by' => $user->id, 'counted_at' => now(),
        ]);

        return $item;
    }

    public function reset(StockTake $take, StockTakeItem $item, User $user): void
    {
        $this->guard($take, $user);
        abort_unless($item->stock_take_id === $take->id, 404);
        $item->result === 'unregistered'
            ? $item->delete()
            : $item->update(['result' => 'pending', 'found_location_id' => null, 'found_grade' => null, 'counted_by' => null, 'counted_at' => null]);
    }

    public function addUnregistered(StockTake $take, string $description, ?int $locationId, ?string $note, User $user): StockTakeItem
    {
        $this->guard($take, $user);

        return $take->items()->create(['result' => 'unregistered', 'description' => mb_substr($description, 0, 250), 'found_location_id' => $locationId,
            'note' => $note, 'counted_by' => $user->id, 'counted_at' => now()]);
    }

    /** Tandai semua yang belum dihitung di satu lokasi sebagai tidak ditemukan. */
    public function markRemainingNotFound(StockTake $take, int $locationId, User $user): int
    {
        $this->guard($take, $user);

        return $take->items()->where('result', 'pending')->where('expected_location_id', $locationId)
            ->update(['result' => 'not_found', 'counted_by' => $user->id, 'counted_at' => now()]);
    }

    /** Tutup sesi; bila $apply, perbarui lokasi & kondisi aset di register sesuai hasil hitung. */
    public function close(StockTake $take, bool $apply, User $user): array
    {
        if (! $user->hasPermission('stocktake.manage')) {
            throw WorkflowException::forbidden();
        }
        if ($take->status !== 'counting') {
            throw WorkflowException::invalid('Sesi sudah ditutup.');
        }

        return DB::transaction(function () use ($take, $apply, $user) {
            $pending = $take->items()->where('result', 'pending')->update(['result' => 'not_found']);
            $moved = $graded = 0;
            if ($apply) {
                foreach ($take->items()->whereIn('result', ['found', 'moved'])->with('asset')->get() as $it) {
                    $a = $it->asset;
                    if (! $a) {
                        continue;
                    }
                    $upd = [];
                    if ($it->result === 'moved' && $it->found_location_id) {
                        $upd['location_id'] = $it->found_location_id;
                        $moved++;
                    }
                    if ($it->found_grade && $it->found_grade !== $a->condition_grade) {
                        $upd['condition_grade'] = $it->found_grade;
                        $graded++;
                    }
                    $upd['verification_status'] = 'verified';
                    $upd['last_inspected_at'] = $it->counted_at ?? now();
                    $a->auditReason = 'Stock opname '.$take->number;
                    $a->statusSource = ['stock_take', $take->id];
                    $a->update($upd);
                }
            }
            $take->update(['status' => 'closed', 'closed_by' => $user->id, 'closed_at' => now(), 'applied' => $apply]);

            return ['not_found_auto' => $pending, 'moved' => $moved, 'graded' => $graded];
        });
    }

    /** Ringkasan hasil per status & per lokasi. */
    public function summary(StockTake $take): array
    {
        $counts = $take->items()->select('result', DB::raw('count(*) as n'))->groupBy('result')->pluck('n', 'result')->all();
        $total = array_sum($counts) - ($counts['unregistered'] ?? 0);
        $done = $total - ($counts['pending'] ?? 0);

        return ['counts' => $counts + array_fill_keys(array_keys(StockTakeItem::RESULTS), 0), 'total' => $total, 'done' => $done,
            'pct' => $total ? round($done / $total * 100) : 0,
            'accuracy' => $done ? round((($counts['found'] ?? 0)) / max(1, $done) * 100) : null];
    }
}
