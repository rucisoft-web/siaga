<?php

namespace Tests\Feature;

use App\Filament\Pages\PenghapusanAset;
use App\Filament\Pages\StockOpname;
use App\Models\Asset;
use App\Models\Disposal;
use App\Models\Role;
use App\Models\User;
use App\Services\DisposalService;
use App\Services\StockTakeService;
use App\Services\WorkflowException;
use App\Support\CurrentProperty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTakeDisposalTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function user(string $role): User
    {
        $u = User::factory()->create(['property_id' => app(CurrentProperty::class)->id(), 'is_active' => true]);
        $u->roles()->attach(Role::where('slug', $role)->value('id'));

        return $u;
    }

    public function test_stock_take_flow_and_disposal_with_segregation_of_duties(): void
    {
        $ac = $this->user('asset-controller');
        $fin = $this->user('finance');
        $this->actingAs($ac);
        $assets = Asset::whereNotNull('location_id')->orderBy('id')->limit(3)->get();
        $this->assertCount(3, $assets);
        $loc = $assets[0]->location_id;

        $svc = app(StockTakeService::class);
        $take = $svc->open(['title' => 'Uji', 'location_id' => null, 'cutoff_date' => now()->toDateString()], $ac);
        $this->assertGreaterThanOrEqual(3, $take->items()->count());

        // aset 0 ditemukan di tempat via kode, aset 1 ditemukan di lokasi lain, aset 2 tidak ditemukan
        $other = \App\Models\Location::where('id', '!=', $assets[1]->location_id)->value('id');
        $this->assertTrue($svc->scan($take, $assets[0]->asset_code, $assets[0]->location_id, 'B', $ac)['ok']);
        $svc->scan($take, $assets[1]->asset_code, $other, null, $ac);
        $svc->mark($take, $take->items()->where('asset_id', $assets[2]->id)->first(), null, null, null, $ac, true);
        $svc->addUnregistered($take, 'Kursi lipat', $loc, null, $ac);
        $this->assertSame('moved', $take->items()->where('asset_id', $assets[1]->id)->value('result'));

        $this->get(StockOpname::getUrl().'?sesi='.$take->id)->assertOk()->assertSee($take->number);
        $r = $svc->close($take, true, $ac);
        $this->assertSame(1, $r['moved']);
        $this->assertSame($other, $assets[1]->fresh()->location_id);
        $this->assertSame('B', $assets[0]->fresh()->condition_grade);
        $this->get(route('stocktake.report', $take))->assertOk()->assertSee('Berita Acara Stock Opname')->assertSee('Kursi lipat')->assertSee($assets[2]->asset_code);

        // penghapusan: pengusul ≠ penyetuju
        $ds = app(DisposalService::class);
        $d = $ds->propose($assets[2], ['method' => 'lost', 'reason' => 'Tidak ditemukan', 'stock_take_id' => $take->id], $ac);
        $this->expectExceptionOnSelfApproval($ds, $d, $fin);
        $ds->decide($d, true, null, $fin);
        $ds->complete($d->fresh(), [], $ac);
        $this->assertSame('lost', $assets[2]->fresh()->lifecycle_status);
        $this->get(PenghapusanAset::getUrl().'?status=semua')->assertOk()->assertSee($d->number);
        $this->get(route('disposal.report', $d))->assertOk()->assertSee('Berita Acara Penghapusan');
    }

    protected function expectExceptionOnSelfApproval(DisposalService $ds, Disposal $d, User $fin): void
    {
        $own = $ds->propose(Asset::whereKeyNot($d->asset_id)->whereNotNull('location_id')->orderByDesc('id')->first(), ['method' => 'scrap', 'reason' => 'Rusak berat'], $fin);
        try {
            $ds->decide($own, true, null, $fin);
            $this->fail('Pengusul tidak boleh menyetujui usulannya sendiri');
        } catch (WorkflowException $e) {
            $this->assertStringContainsString('Pemisahan tugas', $e->getMessage());
        }
    }
}
