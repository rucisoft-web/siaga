<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetFinancial;
use App\Services\DepreciationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepreciationTest extends TestCase
{
    use RefreshDatabase;

    protected function asset(array $fin, array $cat = []): Asset
    {
        $c = new AssetCategory($cat + ['depreciation_method' => 'straight_line', 'useful_life_months' => 48, 'residual_pct' => 0]);
        $a = new Asset;
        $a->setRelation('category', $c);
        $a->setRelation('financial', new AssetFinancial($fin));

        return $a;
    }

    public function test_straight_line_with_half_month_convention(): void
    {
        $s = new DepreciationService;
        // perolehan 10 Jan 2024 → mulai Jan 2024; 48 bln; 4,8 jt → 100 rb/bln
        $d = $s->forAsset($this->asset(['acquisition_cost' => 4800000, 'acquisition_date' => '2024-01-10']), CarbonImmutable::parse('2024-12-31'));
        $this->assertSame(12, $d['months']);
        $this->assertEqualsWithDelta(1200000, $d['accum'], 0.01);
        $this->assertEqualsWithDelta(3600000, $d['book'], 0.01);
        $this->assertEqualsWithDelta(100000, $d['period'], 0.01);
        $this->assertEqualsWithDelta(1200000, $d['ytd'], 0.01);
        // perolehan 20 Jan → mulai Feb
        $d = $s->forAsset($this->asset(['acquisition_cost' => 4800000, 'acquisition_date' => '2024-01-20']), CarbonImmutable::parse('2024-12-31'));
        $this->assertSame(11, $d['months']);
        // habis disusutkan, residu 10%
        $d = $s->forAsset($this->asset(['acquisition_cost' => 1000000, 'acquisition_date' => '2015-01-01'], ['residual_pct' => 10]), CarbonImmutable::parse('2026-10-31'));
        $this->assertTrue($d['fully']);
        $this->assertEqualsWithDelta(100000, $d['book'], 0.01);
        $this->assertEqualsWithDelta(0, $d['period'], 0.01);
    }

    public function test_declining_balance_and_none(): void
    {
        $s = new DepreciationService;
        $a = $this->asset(['acquisition_cost' => 1200000, 'acquisition_date' => '2024-01-01'], ['depreciation_method' => 'declining_balance']);
        $y1 = $s->forAsset($a, CarbonImmutable::parse('2024-12-31'));
        $sl = $s->forAsset($this->asset(['acquisition_cost' => 1200000, 'acquisition_date' => '2024-01-01']), CarbonImmutable::parse('2024-12-31'));
        $this->assertGreaterThan($sl['accum'], $y1['accum']);   // lebih cepat di awal
        $end = $s->forAsset($a, CarbonImmutable::parse('2028-12-31'));
        $this->assertEqualsWithDelta(0, $end['book'], 0.01);     // habis di akhir masa manfaat
        $land = $s->forAsset($this->asset(['acquisition_cost' => 5e9, 'acquisition_date' => '2010-01-01'], ['depreciation_method' => 'none']), CarbonImmutable::parse('2026-10-31'));
        $this->assertEqualsWithDelta(5e9, $land['book'], 0.01);
        $this->assertNull($s->forAsset($this->asset([]), CarbonImmutable::now()));
    }
}
