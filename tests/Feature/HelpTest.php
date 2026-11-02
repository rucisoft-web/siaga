<?php

namespace Tests\Feature;

use App\Filament\Pages\Panduan;
use App\Filament\Pages\PendataanAset;
use App\Models\User;
use App\Support\Help;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_every_screen_has_a_guide_with_existing_images(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutesByName())->keys()
            ->map(fn ($n) => Help::keyForRoute($n))->filter()->unique()->reject(fn ($k) => $k === 'panduan');
        foreach ($routes as $k) {
            $this->assertTrue(Help::has($k), 'panduan belum ada untuk layar: '.$k);
        }
        foreach (glob(resource_path('help/*.md')) as $f) {
            preg_match_all('/\(img\/([a-z0-9._-]+)\)/', file_get_contents($f), $m);
            foreach ($m[1] as $img) {
                $this->assertFileExists(public_path('help/img/'.$img), basename($f).' merujuk gambar yang tidak ada');
            }
        }
        $this->assertCount(collect(Help::GROUPS)->flatten()->count(), collect(Help::index())->flatMap(fn ($g) => $g));
    }

    public function test_help_button_and_index_render(): void
    {
        $u = User::where('email', DemoSeeder::email('eng.admin'))->first();
        $u->forceFill(['must_change_password' => false])->save();
        $this->actingAs($u)->get(PendataanAset::getUrl())->assertOk()->assertSee('ac-help-btn', false)->assertSee('Peralatan yang dibawa');
        $this->get(Panduan::getUrl())->assertOk()->assertSee('Pendataan aset awal')->assertSee('Impor master aset (Excel)');
        $this->get(Panduan::getUrl(['baca' => 'pendataan']))->assertOk()->assertSee('Cetak / simpan PDF')->assertSee('help/img/pendataan-cetak.png', false);
        $this->get(Panduan::getUrl(['baca' => '../.env']))->assertOk()->assertDontSee('APP_KEY');
    }

    public function test_implementation_guide_page(): void
    {
        $u = User::where('email', DemoSeeder::email('teknisi1'))->first();
        $u->forceFill(['must_change_password' => false])->save();
        $r = $this->actingAs($u)->get(\App\Filament\Pages\PanduanImplementasi::getUrl())->assertOk();
        foreach (['Lingkup kerja', 'Jadwal', 'Sumber daya yang perlu disiapkan', 'Cek kualitas pendataan aset', 'Struktur organisasi pengelola aset', 'Komunikasi', 'Daftar risiko dan penanggulangan', 'Pengadaan yang diperlukan'] as $s) {
            $r->assertSee($s);
        }
        $r->assertSee('id="8-pengadaan-yang-diperlukan"', false)->assertDontSee('ac-help-btn', false);
    }
}
