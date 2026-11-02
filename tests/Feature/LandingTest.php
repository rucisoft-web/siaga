<?php

namespace Tests\Feature;

use App\Filament\Pages\DasborPimpinan;
use App\Models\Property;
use App\Models\User;
use App\Support\GuestAccess;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function demo(): Property
    {
        $p = Property::create(['code' => 'GPCH', 'name' => 'Grand Pasundan Convention Hotel (demo)', 'timezone' => 'Asia/Jakarta', 'is_active' => true]);
        GuestAccess::ensure($p);

        return $p;
    }

    public function test_landing_is_off_by_default(): void
    {
        config(['assetcare.guest_mode' => false, 'assetcare.landing' => false]);
        $this->get('/')->assertRedirect('/admin');
        $this->get('/panduan')->assertNotFound();
        $this->post('/demo/masuk', ['dataset' => 'GPCH'])->assertNotFound();
    }

    public function test_landing_lists_demo_and_guest_login_is_read_only(): void
    {
        config(['assetcare.guest_mode' => true, 'assetcare.landing' => true]);
        $p = $this->demo();
        $this->get('/')->assertOk()
            ->assertSee('Masalah pengelolaan aset tetap')->assertSee('Kenapa terjadi')->assertSee('Solusi SIAGA')->assertSee('Workshop pengenalan 2 hari')
            ->assertSee('id="angka"', false)->assertSee('chart.umd.min.js', false)
            ->assertSee('Grand Pasundan Convention Hotel')->assertSee('Masuk sebagai tamu')->assertSee('value="GPCH"', false)
            ->assertDontSee('value="HTL"', false);
        $this->get('/admin/login')->assertOk()->assertSee('Masuk sebagai tamu')->assertSee(route('landing.guest'), false);
        $this->get('/panduan')->assertOk()->assertSee('Dasbor pimpinan');
        $this->get('/panduan/implementasi')->assertOk()->assertSee('Pengadaan yang diperlukan');
        $this->get('/panduan/tidak-ada')->assertNotFound();

        $this->post('/demo/masuk', ['dataset' => 'XXX'])->assertRedirect();
        $this->assertGuest();
        $this->post('/demo/masuk', ['dataset' => 'GPCH'])->assertRedirect(DasborPimpinan::getUrl());
        $u = auth()->user();
        $this->assertSame(GuestAccess::email($p), $u->email);
        $this->assertSame($p->id, $u->property_id);
        $this->assertTrue($u->hasRole('guest'));
        $this->assertFalse($u->hasPermission('request.create'));
        $this->assertFalse($u->hasPermission('asset.edit'));
        $this->get(DasborPimpinan::getUrl())->assertOk()->assertSee('Sorotan untuk pimpinan')->assertSee('ac-guest-banner', false)->assertSee('Kondisi aset per lokasi');
        $this->get('/admin/profile')->assertRedirect();
    }

    public function test_leadership_dashboard_renders_with_data(): void
    {
        $u = User::where('email', DemoSeeder::email('gm'))->first() ?? User::whereHas('roles', fn ($q) => $q->where('slug', 'gm'))->first();
        $u->forceFill(['must_change_password' => false])->save();
        $this->actingAs($u)->get(DasborPimpinan::getUrl())->assertOk()
            ->assertSee('1 · Kondisi', false)->assertSee('Tren mingguan')->assertSee('Biaya pemeliharaan per bulan')->assertSee('chart.umd.min.js', false);
    }
}
