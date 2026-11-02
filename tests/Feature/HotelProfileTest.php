<?php

namespace Tests\Feature;

use App\Filament\Pages\ProfilHotel;
use App\Models\Asset;
use App\Models\Property;
use App\Models\User;
use App\Support\Branding;
use App\Support\Sequence;
use Database\Seeders\ReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Produk umum: identitas hotel dari Profil hotel, bukan dari kode. */
class HotelProfileTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function admin(): User
    {
        return User::where('email', ReferenceSeeder::ADMIN_EMAIL)->firstOrFail();
    }

    public function test_install_command_sets_up_any_hotel(): void
    {
        $this->artisan('assetcare:install', [
            '--hotel' => 'Hotel Kenanga Makassar', '--code' => 'HKM', '--timezone' => 'Asia/Makassar',
            '--admin-email' => 'it@kenanga.test', '--admin-password' => 'Kenanga2026ok', '--fresh' => true, '--no-interaction' => true,
        ])->assertSuccessful();

        $p = Property::sole();
        $this->assertSame('Hotel Kenanga Makassar', $p->name);
        $this->assertSame('HKM', $p->code);
        Branding::flush();
        $this->assertSame('Asia/Makassar', Branding::timezone());
        $this->assertSame('WITA', Branding::timezoneLabel());
        $this->assertSame('HKM-AST-000001', Sequence::assetCode($p->id));
        $this->assertTrue(User::where('email', 'it@kenanga.test')->first()->hasPermission('settings.manage'));
        $this->assertFalse(User::where('email', ReferenceSeeder::ADMIN_EMAIL)->exists(), 'tidak ada akun admin bawaan');
        $this->assertSame(0, Asset::withoutGlobalScopes()->count(), 'tanpa --demo tidak ada data contoh');
    }

    public function test_profile_page_changes_name_colors_and_logo(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());

        Livewire::test(ProfilHotel::class)
            ->set('data.name', 'Hotel Anggrek')
            ->set('data.primary_color', '#0f5257')
            ->set('data.accent_color', '#E07A1F')
            ->set('data.timezone', 'Asia/Jayapura')
            ->set('data.logo_path', UploadedFile::fake()->image('logo.png', 400, 120))
            ->call('save')
            ->assertHasNoErrors();

        Branding::flush();
        $this->assertSame('Hotel Anggrek', Branding::hotelName());
        $this->assertSame('#0F5257', Branding::primary());
        $this->assertSame('WIT', Branding::timezoneLabel());
        $this->assertStringContainsString('--p:#0F5257', Branding::cssVars());
        $this->assertNotNull(Branding::logoUrl());
        $this->get(Branding::logoUrl())->assertOk();

        $this->get('/admin')->assertOk()->assertSee('Hotel Anggrek')->assertSee('--p:#0F5257', false);
    }

    public function test_property_code_locked_once_assets_exist(): void
    {
        $this->actingAs($this->admin());
        $code = Property::first()->code;
        $this->assertTrue(Asset::withoutGlobalScopes()->exists());

        Livewire::test(ProfilHotel::class)->set('data.code', 'ZZZ')->call('save')->assertHasNoErrors();
        $this->assertSame($code, Property::first()->code);
    }

    public function test_invalid_color_rejected_and_page_requires_permission(): void
    {
        $this->actingAs($this->admin());
        Livewire::test(ProfilHotel::class)->set('data.primary_color', 'red;}body{')->call('save')->assertHasErrors(['data.primary_color']);

        $tech = User::where('email', \Database\Seeders\DemoSeeder::email('teknisi1'))->first();
        $tech->forceFill(['must_change_password' => false])->save();
        $this->actingAs($tech);
        $this->get(ProfilHotel::getUrl())->assertForbidden();
    }

    public function test_no_gpch_branding_left_in_ui(): void
    {
        $this->actingAs($this->admin());
        $html = $this->get('/admin')->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/GPCH|Pasundan|wayang/i', $html);
        $this->get('/admin/login')->assertDontSee('Pasundan');
    }
}
