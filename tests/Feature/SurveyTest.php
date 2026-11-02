<?php

namespace Tests\Feature;

use App\Filament\Pages\PendataanAset;
use App\Filament\Pages\StokLabel;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLabel;
use App\Models\Location;
use App\Models\RoomAssetTemplate;
use App\Models\RoomSurvey;
use App\Models\User;
use App\Services\LabelService;
use App\Services\QrResolver;
use App\Services\RoomTemplateService;
use App\Services\SurveyService;
use App\Services\WorkflowException;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Pendataan aset awal: petugas keliling, label per kamar atau stok label kosong. */
class SurveyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function surveyor(): User
    {
        $u = User::factory()->create(['email' => 'petugas@hotel.test', 'must_change_password' => false, 'is_active' => true, 'property_id' => \App\Models\Property::first()->id]);
        $u->roles()->attach(\App\Models\Role::where('slug', 'surveyor')->value('id'), ['property_id' => $u->property_id]);

        return $u->fresh();
    }

    /** Generate aset draft untuk kamar 301–302 dari template Deluxe. */
    protected function generateRooms(): array
    {
        $ac = User::where('email', DemoSeeder::email('eng.admin'))->first();
        $this->actingAs($ac);
        $rooms = Location::whereIn('code', ['GU-L3-301', 'GU-L3-302'])->orderBy('sort_key')->pluck('id')->all();
        $svc = app(RoomTemplateService::class);
        $tpl = RoomAssetTemplate::where('code', 'TPL-DLX')->firstOrFail();
        $batch = $svc->createBatch($tpl, $rooms, $svc->preview($tpl, $rooms), $ac, 'uji-survey');
        $svc->commit($batch, [], $ac);

        return $rooms;
    }

    public function test_room_checklist_with_preprinted_labels(): void
    {
        [$r301] = $this->generateRooms();
        $room = Location::find($r301);
        $u = $this->surveyor();
        $this->actingAs($u);
        $svc = app(SurveyService::class);

        $items = $svc->items($room);
        $this->assertCount(7, $items, 'template Deluxe = 7 unit');
        $this->assertSame('todo', $svc->itemState($items[0]));

        // tidak bisa selesai sebelum semua item dicek
        try {
            $svc->finish($room, $u);
            $this->fail('harus ditolak');
        } catch (WorkflowException $e) {
            $this->assertStringContainsString('belum dicek', $e->getMessage());
        }

        // pindai label (token dari label tercetak) → terpasang; catat kondisi
        foreach ($items->take(6) as $a) {
            $r = $svc->scan($room, $a->activeLabel->url(), $u);
            $this->assertSame($a->id, $r['asset_id']);
            $svc->record($a->fresh(), true, 'B', ['brand' => 'Merek X'], $u);
        }
        $last = $items[6];
        $svc->record($last, false, null, [], $u); // tidak ada di kamar

        $s = $svc->finish($room, $u);
        $this->assertSame('done', $s->status);
        $this->assertSame(6, $s->found_count);
        $this->assertSame(1, $s->missing_count);
        $this->assertSame('done', $svc->itemState($items[0]->fresh()));
        $this->assertSame('discrepancy', $last->fresh()->verification_status);
        $this->assertSame('B', $items[0]->fresh()->condition_grade);

        // label kamar lain ditolak (tertukar)
        $other = Asset::where('location_id', '!=', $room->id)->whereHas('activeLabel')->first();
        $r = $svc->scan($room, $other->activeLabel->url(), $u);
        $this->assertSame('warning', $r['level']);
    }

    public function test_blank_stock_labels_bind_on_site_and_extra_asset(): void
    {
        [$r301] = $this->generateRooms();
        $room = Location::find($r301);
        $admin = User::where('email', DemoSeeder::email('eng.admin'))->first();
        $batch = app(LabelService::class)->issueBlankBatch(3, 'roll-50x30', 1, $admin);
        $this->assertSame(3, $batch->label_count);
        $blanks = AssetLabel::where('status', 'unassigned')->orderBy('id')->get();
        $this->assertCount(3, $blanks);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]+-QR-\d{6}$/', $blanks[0]->stock_code);
        $this->assertSame(200, $this->actingAs($admin)->get(route('labels.pdf', $batch))->status());

        // scan biasa: belum terhubung
        $this->assertSame('blank', app(QrResolver::class)->resolve($blanks[0]->url())['type']);

        $u = $this->surveyor();
        $this->actingAs($u);
        $svc = app(SurveyService::class);
        $ac = $svc->items($room)->first();
        $oldLabel = $ac->activeLabel;

        // 1) pindai label stok → diminta pilih aset → hubungkan (label lama yang belum dicetak dinonaktifkan)
        $r = $svc->scan($room, $blanks[0]->url(), $u);
        $this->assertSame($blanks[0]->id, $r['blank_label_id']);
        $r = $svc->attachBlank($room, $blanks[0], $ac, $u);
        $this->assertSame('installed', $blanks[0]->fresh()->currentInstallationStatus());
        $this->assertSame($ac->id, $blanks[0]->fresh()->asset_id);
        $this->assertSame('revoked', $oldLabel->fresh()->status);
        $this->assertSame('asset', app(QrResolver::class)->resolve($blanks[0]->stock_code)['type'], 'nomor stok bisa diketik');

        // label stok yang sama tidak bisa dipakai dua kali
        $this->expectExceptionMessage('sudah terhubung');
        try {
            $svc->attachBlank($room, $blanks[0]->fresh(), $svc->items($room)[1], $u);
        } finally {
            // 2) aset tambahan dengan label stok + foto
            Storage::fake('local');
            $cat = AssetCategory::where('code', 'FURN')->value('id');
            $extra = $svc->addExtra($room, ['name' => 'Kursi kerja', 'category_id' => $cat, 'condition_grade' => 'A'], $u, $blanks[1]->fresh(), UploadedFile::fake()->image('kursi.jpg'));
            $this->assertSame('verified', $extra->verification_status);
            $this->assertSame($blanks[1]->id, $extra->activeLabel->id);
            $this->assertSame(1, $extra->attachments()->count());
            $this->assertSame(8, $svc->items($room)->count());
        }
    }

    public function test_pages_render_for_surveyor_and_supervisor(): void
    {
        [$r301] = $this->generateRooms();
        $u = $this->surveyor();
        $this->actingAs($u);
        $this->get(PendataanAset::getUrl())->assertOk()->assertSee('Pilih kamar');
        $this->get(PendataanAset::getUrl(['room' => $r301]))->assertOk()->assertSee('Isi kamar (7 item)')->assertSee('Selesai kamar ini');
        $this->get(StokLabel::getUrl())->assertForbidden(); // petugas tidak mencetak

        Livewire::test(PendataanAset::class)->call('openRoom', $r301)
            ->call('focus', Asset::where('location_id', $r301)->value('id'))
            ->set('cond', 'A')->call('saveItem')
            ->assertSee('kondisi A dicatat');
        $this->assertSame(1, Asset::where('location_id', $r301)->where('verification_status', 'verified')->count());
        $this->assertSame('in_progress', RoomSurvey::where('location_id', $r301)->value('status'));

        $gm = User::where('email', DemoSeeder::email('gm'))->first();
        $gm->forceFill(['must_change_password' => false])->save();
        $this->flushSession();
        $this->actingAs($gm)->get(PendataanAset::getUrl(['tab' => 'progres']))->assertOk()->assertSee('Progres per lantai');

        $admin = User::where('email', DemoSeeder::email('eng.admin'))->first();
        $admin->forceFill(['must_change_password' => false])->save();
        $this->flushSession();
        $this->actingAs($admin)->get(StokLabel::getUrl())->assertOk()->assertSee('Cetak stok baru');
    }

    public function test_field_printing_and_quantity_counts(): void
    {
        [$r301] = $this->generateRooms();
        $room = Location::find($r301);
        $u = $this->surveyor();
        $this->actingAs($u);
        $svc = app(SurveyService::class);

        // petugas boleh mencetak label kamar yang didata, langsung dari browser (printer portable)
        $this->assertSame(7, $svc->unprintedCount($room));
        $b = $svc->printLabels($room, $u, 'roll-50x30');
        $this->assertSame(7, $b->label_count);
        $this->assertSame(0, $svc->unprintedCount($room));
        $html = $this->get(route('labels.print', $b))->assertOk()->assertSee('window.print', false)->assertSee('size: 50mm 30mm', false)->getContent();
        $this->assertSame(7, substr_count($html, 'class="qr"'));
        $this->get(route('labels.pdf', $b))->assertOk(); // batch pendataan miliknya sendiri
        try {
            $svc->printLabels($room, $u, 'roll-50x30');
            $this->fail('tidak ada lagi yang perlu dicetak');
        } catch (WorkflowException $e) {
            $this->assertStringContainsString('Tidak ada label', $e->getMessage());
        }
        $first = $svc->items($room)->first();
        $re = $svc->printLabels($room, $u, 'roll-40x30', [$first->id]);
        $this->assertSame('reprint', $re->purpose);
        $this->assertSame(1, $re->label_count);

        // batch pendataan petugas lain tidak bisa dibuka
        $other = User::factory()->create(['email' => 'p2@hotel.test', 'must_change_password' => false, 'is_active' => true, 'property_id' => $room->property_id]);
        $other->roles()->attach(\App\Models\Role::where('slug', 'surveyor')->value('id'), ['property_id' => $room->property_id]);
        $this->flushSession();
        $this->actingAs($other->fresh())->get(route('labels.print', $b))->assertForbidden();

        // item hitungan wajib dihitung sebelum selesai
        $this->flushSession();
        $this->actingAs($u);
        $g = \App\Models\AssetQuantityGroup::create(['location_id' => $room->id, 'category_id' => $first->category_id, 'name' => 'Bantal · 301', 'unit' => 'unit', 'expected_qty' => 4, 'status' => 'draft']);
        foreach ($svc->items($room) as $a) {
            $svc->record($a, true, 'A', [], $u);
        }
        try {
            $svc->finish($room, $u);
            $this->fail('harus ditolak');
        } catch (WorkflowException $e) {
            $this->assertStringContainsString('belum dihitung', $e->getMessage());
        }
        Livewire::test(PendataanAset::class)->call('openRoom', $r301)->set('counts.'.$g->id, '3')->call('saveCount', $g->id)->assertSee('standar 4');
        $this->assertSame(3.0, (float) $g->fresh()->on_hand);
        $this->assertSame('done', $svc->finish($room, $u)->status);
    }
}
