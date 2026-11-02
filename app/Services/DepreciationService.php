<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Penyusutan aset tetap (PSAK 16) dihitung per bulan:
 * - garis lurus: (harga perolehan − nilai residu) / masa manfaat (bulan)
 * - saldo menurun ganda: tarif tahunan 2 / masa manfaat (tahun), diterapkan bulanan pada nilai buku, tidak di bawah residu;
 *   sisa dihabiskan pada bulan terakhir masa manfaat
 * Penyusutan dimulai bulan berikutnya bila perolehan setelah tanggal 15 (konvensi setengah bulan).
 * Tidak ada jurnal yang disimpan: semua angka dihitung ulang dari data perolehan sehingga selalu konsisten.
 */
class DepreciationService
{
    public const METHODS = ['straight_line' => 'Garis lurus', 'declining_balance' => 'Saldo menurun ganda', 'none' => 'Tidak disusutkan (mis. tanah)'];

    /** @return array{cost: float, residual: float, method: string, life: int, start: ?CarbonImmutable, monthly: float, months: int, accum: float, book: float, period: float, ytd: float, end: ?CarbonImmutable, fully: bool}|null */
    public function forAsset(Asset $a, CarbonImmutable $asOf): ?array
    {
        $f = $a->financial;
        if (! $f || ! (float) $f->acquisition_cost) {
            return null;
        }
        $cat = $a->category;
        $cost = (float) $f->acquisition_cost;
        $method = $f->depreciation_method ?: ($cat->depreciation_method ?? 'straight_line');
        $life = (int) ($f->useful_life_months ?: ($cat->useful_life_months ?? 96));
        $residual = $f->residual_value !== null ? (float) $f->residual_value : round($cost * (float) ($cat->residual_pct ?? 0) / 100, 2);
        $startDate = $f->depreciation_start ?? $f->acquisition_date ?? $a->in_service_date ?? $a->purchase_date;
        $start = $startDate ? CarbonImmutable::parse($startDate)->startOfMonth() : null;
        if ($start && $startDate && CarbonImmutable::parse($startDate)->day > 15) {
            $start = $start->addMonth();
        }
        $base = ['cost' => $cost, 'residual' => $residual, 'method' => $method, 'life' => $life, 'start' => $start];

        if ($method === 'none' || ! $start || $life < 1) {
            return $base + ['monthly' => 0.0, 'months' => 0, 'accum' => 0.0, 'book' => $cost, 'period' => 0.0, 'ytd' => 0.0, 'end' => null, 'fully' => false];
        }
        $asOfM = $asOf->startOfMonth();
        $elapsed = $asOfM->lt($start) ? 0 : (int) $start->diffInMonths($asOfM) + 1;
        $months = min($life, $elapsed);
        $accumAt = fn (int $m) => $this->accumAfter($method, $cost, $residual, $life, $m);
        $accum = $accumAt($months);
        $period = $months > 0 && $elapsed <= $life ? $accum - $accumAt($months - 1) : 0.0;
        $monthsBeforeYear = $asOfM->startOfYear()->lt($start) ? 0 : min($life, (int) $start->diffInMonths($asOfM->startOfYear()));
        $ytd = $accum - $accumAt($monthsBeforeYear);

        return $base + [
            'monthly' => $life ? ($cost - $residual) / $life : 0.0,
            'months' => $months, 'accum' => round($accum, 2), 'book' => round($cost - $accum, 2),
            'period' => round($period, 2), 'ytd' => round($ytd, 2),
            'end' => $start->addMonths($life - 1)->endOfMonth(), 'fully' => $months >= $life,
        ];
    }

    protected function accumAfter(string $method, float $cost, float $residual, int $life, int $m): float
    {
        if ($m <= 0) {
            return 0.0;
        }
        $base = max(0, $cost - $residual);
        if ($method !== 'declining_balance') {
            return min($base, $base / $life * $m);
        }
        if ($m >= $life) {
            return $base;
        }
        $rate = 2 / ($life / 12) / 12;
        $book = $cost;
        for ($i = 0; $i < $m; $i++) {
            $book = max($residual, $book - $book * $rate);
        }

        return $cost - $book;
    }

    /**
     * Daftar aset tetap dengan nilai buku per tanggal.
     *
     * @return Collection<int, array{asset: Asset, d: array}>
     */
    public function register(CarbonImmutable $asOf, ?int $categoryId = null, ?array $locationIds = null): Collection
    {
        return Asset::query()->with(['financial', 'category', 'location:id,name,full_path'])
            ->whereHas('financial', fn ($q) => $q->where('acquisition_cost', '>', 0))
            ->whereNotIn('lifecycle_status', ['disposed', 'lost'])
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($locationIds !== null, fn ($q) => $q->whereIn('location_id', $locationIds))
            ->orderBy('asset_code')->get()
            ->map(fn (Asset $a) => ['asset' => $a, 'd' => $this->forAsset($a, $asOf)])
            ->filter(fn ($x) => $x['d'] !== null)->values();
    }
}
