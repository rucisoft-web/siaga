<?php

namespace Tests\Feature;

use App\Filament\Pages\ImporMaster;
use App\Models\AssetQuantityGroup;
use App\Models\Location;
use App\Models\RoomAssetTemplate;
use App\Models\RoomType;
use App\Models\User;
use App\Services\MasterImportService;
use App\Services\RoomTemplateService;
use App\Services\SurveyService;
use App\Services\WorkflowException;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class MasterImportTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = base_path('tests/fixtures/master-aset-gpch.xlsx');
    }

    protected function admin(): User
    {
        $u = User::where('email', DemoSeeder::email('eng.admin'))->first();
        $u->forceFill(['must_change_password' => false])->save();

        return $u;
    }

    public function test_import_gpch_workbook(): void
    {
        $u = $this->admin();
        $this->actingAs($u);
        $svc = app(MasterImportService::class);

        $dry = $svc->import($this->file, $u, true);
        $this->assertSame(14, $dry['summary']['template']);
        $this->assertSame(0, RoomType::where('name', 'Deluxe A King')->count(), 'uji coba tidak menyimpan');

        $r = $svc->import($this->file, $u, false, true);
        $s = $r['summary'];
        $this->assertSame(14, $s['tipe_kamar']);
        $this->assertSame(14, $s['template']);
        $this->assertSame(394, $s['item_template']);
        $this->assertSame(14, $s['ruang_meeting'], '13 ruang meeting + Malibu Dome');
        $this->assertSame(383, $s['item_estimasi']);
        $this->assertSame(394, $s['baris_kamar_dilewati']);
        $this->assertSame(3, $s['kamar']);
        $this->assertTrue(collect($r['warnings'])->contains(fn ($w) => str_contains($w, '9999')));

        $tpl = RoomAssetTemplate::whereHas('roomType', fn ($q) => $q->where('name', 'Deluxe A King'))->firstOrFail();
        $this->assertSame('published', $tpl->status);
        $this->assertSame('quantity', $tpl->items()->where('standard_name', 'Bantal (terpasang)')->value('mode'));
        $this->assertSame('quantity', $tpl->items()->where('standard_name', 'Lampu utama/plafon')->value('mode'), 'satuan titik');
        $this->assertSame('individual', $tpl->items()->where('standard_name', 'Televisi')->value('mode'));

        // area umum per gedung: Lobby di Main Building & West Wing terpisah
        $this->assertSame(2, Location::where('name', 'Main & West Lobby')->count());
        $genset = AssetQuantityGroup::where('name', 'Genset utama')->first();
        $this->assertSame(2.0, (float) $genset->expected_qty);
        $this->assertNull($genset->on_hand);

        // diulang: tidak menggandakan
        $again = $svc->import($this->file, $u);
        $this->assertSame(0, $again['summary']['template'] + $again['summary']['item_estimasi'] + $again['summary']['kamar'] + $again['summary']['tipe_kamar']);

        // generate kamar 201 dari template → checklist berisi aset berlabel + item hitungan
        $room = Location::where('room_ref', '201')->firstOrFail();
        $ts = app(RoomTemplateService::class);
        $ts->commit($ts->createBatch($tpl, [$room->id], $ts->preview($tpl, [$room->id]), $u, 'uji impor'), [], $u);
        $survey = app(SurveyService::class);
        $this->assertGreaterThan(10, $survey->items($room)->count());
        $this->assertTrue($survey->groups($room)->contains(fn ($g) => str_starts_with($g->name, 'Bantal')));
    }

    public function test_import_page_and_permissions(): void
    {
        $u = $this->admin();
        $this->actingAs($u)->get(ImporMaster::getUrl())->assertOk()->assertSee('Rujukan Aset Kamar');
        Livewire::test(ImporMaster::class)
            ->set('file', UploadedFile::fake()->createWithContent('master.xlsx', file_get_contents($this->file)))
            ->call('check')->assertSee('uji coba')->assertSee('Template isi kamar');
        $this->assertSame(0, RoomType::where('name', 'Deluxe A King')->count());

        $tech = User::where('email', DemoSeeder::email('teknisi1'))->first();
        $this->expectException(WorkflowException::class);
        app(MasterImportService::class)->import($this->file, $tech);
    }
}
