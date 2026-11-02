<?php

namespace Tests\Feature;

use App\Filament\Pages\JadwalPm;
use App\Models\Asset;
use App\Models\PmSchedule;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Services\PmService;
use App\Support\CurrentProperty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PmScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function user(string $role, ?int $pivotProperty = -1): User
    {
        $pid = app(CurrentProperty::class)->id();
        $u = User::factory()->create(['property_id' => $pid, 'is_active' => true]);
        $u->roles()->attach(Role::where('slug', $role)->value('id'), ['property_id' => $pivotProperty === -1 ? $pid : $pivotProperty]);

        return $u;
    }

    public function test_pm_schedule_generates_work_order_once_and_advances(): void
    {
        $ce = $this->user('chief-engineering');
        $this->actingAs($ce);
        $asset = Asset::whereNotNull('location_id')->first();
        $today = PmService::today();
        $s = PmSchedule::create(['title' => 'Servis AC', 'type' => 'preventive', 'asset_id' => $asset->id, 'interval_days' => 30,
            'next_due' => $today->addDays(2)->toDateString(), 'lead_days' => 3, 'priority' => 'P4', 'checklist' => "Bersihkan filter\nCek freon", 'is_active' => true]);
        $later = PmSchedule::create(['title' => 'Kalibrasi', 'type' => 'calibration', 'asset_id' => $asset->id, 'interval_days' => 365,
            'next_due' => $today->addDays(40)->toDateString(), 'lead_days' => 14, 'is_active' => true]);

        $made = app(PmService::class)->runDue();
        $this->assertCount(1, $made);
        $wo = WorkOrder::where('pm_schedule_id', $s->id)->first();
        $this->assertSame('preventive', $wo->type);
        $this->assertSame($asset->id, $wo->asset_id);
        $this->assertStringContainsString('☐ Cek freon', $wo->description);
        $this->assertSame($today->addDays(32)->toDateString(), $s->fresh()->next_due->toDateString());
        $this->assertNull($later->fresh()->last_work_order_id);

        // dijalankan ulang: tidak ada WO ganda untuk tanggal yang sama
        $this->assertCount(0, app(PmService::class)->runDue());
        $s->fresh()->update(['next_due' => $today->addDays(2)->toDateString()]);
        $this->assertNull(app(PmService::class)->generate($s->fresh(), $ce));

        $this->get(JadwalPm::getUrl())->assertOk()->assertSee('Servis AC')->assertSee('Kalibrasi');
        $this->artisan('siaga:pm-run')->assertSuccessful();
    }

    public function test_group_schedule_and_expiry_reminders(): void
    {
        $ce = $this->user('chief-engineering');
        $this->actingAs($ce);
        $a = Asset::whereNotNull('category_id')->whereNotNull('location_id')->first();
        $s = PmSchedule::create(['title' => 'Inspeksi kelompok', 'type' => 'inspection', 'category_id' => $a->category_id, 'interval_days' => 90,
            'next_due' => PmService::today()->toDateString(), 'lead_days' => 0, 'is_active' => true]);
        $n = app(PmService::class)->targetAssets($s)->count();
        $this->assertGreaterThanOrEqual(1, $n);
        $wo = app(PmService::class)->generate($s, $ce);
        $this->assertSame($n === 1 ? $a->id : null, $n === 1 ? $wo->asset_id : null);

        $a->update(['warranty_until' => PmService::today()->addDays(5)->toDateString()]);
        Vendor::query()->first()?->update(['contract_until' => PmService::today()->addDays(20)->toDateString()]);
        $exp = app(PmService::class)->expiring(60);
        $this->assertTrue($exp->contains(fn ($r) => $r['kind'] === 'Garansi aset' && $r['days'] === 5));
        $first = app(PmService::class)->remind();
        $this->assertGreaterThanOrEqual(1, $first);
        $this->assertSame(0, app(PmService::class)->remind(), 'pengingat tidak dikirim dua kali');
    }
}
