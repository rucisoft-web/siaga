<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\Disposal;
use App\Models\Location;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Services\DisposalService;
use App\Services\StockTakeService;
use App\Services\WorkflowException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/** Stock opname / inventarisasi fisik berkala: buka sesi per cakupan lokasi, hitung per ruang dengan scan QR, tutup & berita acara. */
class StockOpname extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengendalian Aset';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Stock opname';

    protected static ?string $title = 'Stock opname aset';

    protected static ?string $slug = 'stock-opname';

    protected string $view = 'filament.pages.stock-opname';

    #[Url]
    public ?int $sesi = null;

    #[Url]
    public string $tab = 'hitung';

    #[Url]
    public ?int $ruang = null;

    public ?string $grade = null;

    public ?string $message = null;

    public string $level = 'info';

    public array $form = ['title' => '', 'location_id' => null, 'cutoff_date' => null];

    public array $baru = ['description' => '', 'note' => ''];

    public bool $apply = true;

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return (bool) ($u?->hasPermission('stocktake.count') || $u?->hasPermission('stocktake.manage') || $u?->hasPermission('asset.view_financial') || $u?->hasPermission('audit.view'));
    }

    public function mount(): void
    {
        $this->form['cutoff_date'] = now()->toDateString();
        $this->form['title'] = 'Stock opname '.now()->translatedFormat('F Y');
    }

    protected function take(): ?StockTake
    {
        return $this->sesi ? StockTake::with('location')->find($this->sesi) : null;
    }

    protected function svc(): StockTakeService
    {
        return app(StockTakeService::class);
    }

    protected function run(callable $fn, ?string $ok = null): void
    {
        try {
            $fn();
            if ($ok) {
                Notification::make()->title($ok)->success()->send();
            }
        } catch (WorkflowException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }

    public function create(): void
    {
        $this->validate(['form.title' => 'required|string|max:150', 'form.location_id' => 'nullable|integer', 'form.cutoff_date' => 'required|date']);
        $this->run(function () {
            $t = $this->svc()->open($this->form, auth()->user());
            $this->sesi = $t->id;
            $this->tab = 'hitung';
        }, 'Sesi stock opname dibuka. Daftar aset sudah disalin sebagai acuan hitung.');
    }

    public function scan(string $text, string $channel = 'scanner'): void
    {
        $t = $this->take();
        if (! $t) {
            return;
        }
        try {
            $r = $this->svc()->scan($t, $text, $this->ruang, $this->grade, auth()->user());
            $this->message = $r['message'];
            $this->level = $r['ok'] ? (($r['item']->result ?? '') === 'moved' ? 'warn' : 'ok') : 'err';
        } catch (WorkflowException $e) {
            $this->message = $e->getMessage();
            $this->level = 'err';
        }
    }

    public function found(int $item, ?string $grade = null): void
    {
        $t = $this->take();
        $this->run(fn () => $this->svc()->mark($t, $t->items()->findOrFail($item), $this->ruang, $grade ?: $this->grade, null, auth()->user()));
    }

    public function missing(int $item): void
    {
        $t = $this->take();
        $this->run(fn () => $this->svc()->mark($t, $t->items()->findOrFail($item), null, null, null, auth()->user(), true));
    }

    public function undo(int $item): void
    {
        $t = $this->take();
        $this->run(fn () => $this->svc()->reset($t, $t->items()->findOrFail($item), auth()->user()));
    }

    public function remainingMissing(): void
    {
        $t = $this->take();
        if ($t && $this->ruang) {
            $this->run(fn () => $this->svc()->markRemainingNotFound($t, $this->ruang, auth()->user()), 'Sisa aset di ruang ini ditandai tidak ditemukan.');
        }
    }

    public function addNew(): void
    {
        $this->validate(['baru.description' => 'required|string|max:250', 'baru.note' => 'nullable|string|max:250']);
        $t = $this->take();
        $this->run(function () use ($t) {
            $this->svc()->addUnregistered($t, $this->baru['description'], $this->ruang, $this->baru['note'] ?: null, auth()->user());
            $this->baru = ['description' => '', 'note' => ''];
        }, 'Temuan aset tak terdaftar dicatat. Daftarkan lewat Register aset setelah opname.');
    }

    public function close(): void
    {
        $t = $this->take();
        $this->run(function () use ($t) {
            $r = $this->svc()->close($t, $this->apply, auth()->user());
            $this->tab = 'hasil';
            Notification::make()->title('Sesi ditutup')->body(($r['not_found_auto'] ? $r['not_found_auto'].' aset belum dihitung dicatat tidak ditemukan. ' : '').($this->apply ? $r['moved'].' lokasi dan '.$r['graded'].' kondisi diperbarui di register.' : 'Register tidak diubah.'))->success()->send();
        });
    }

    public function proposeDisposal(int $item): void
    {
        $t = $this->take();
        $it = $t->items()->with('asset')->findOrFail($item);
        $this->run(fn () => app(DisposalService::class)->propose($it->asset, [
            'method' => 'lost', 'reason' => 'Tidak ditemukan pada stock opname '.$t->number, 'stock_take_id' => $t->id,
        ], auth()->user()), 'Usulan penghapusan dibuat. Menunggu persetujuan di menu Penghapusan aset.');
    }

    protected function getViewData(): array
    {
        $u = auth()->user();
        $t = $this->take();
        $data = ['take' => $t, 'canManage' => $u->hasPermission('stocktake.manage'), 'canCount' => $u->hasPermission('stocktake.count'),
            'canDispose' => $u->hasPermission('disposal.request'), 'grades' => Asset::CONDITIONS, 'results' => StockTakeItem::RESULTS];
        if (! $t) {
            return $data + [
                'sessions' => StockTake::with('location', 'creator')->latest()->limit(30)->get(),
                'progress' => StockTakeItem::query()->whereIn('stock_take_id', StockTake::latest()->limit(30)->pluck('id'))
                    ->selectRaw("stock_take_id, count(*) as n, sum(result <> 'pending') as done, sum(result = 'not_found') as nf")
                    ->groupBy('stock_take_id')->get()->keyBy('stock_take_id'),
                'locations' => Location::options(),
            ];
        }
        $sum = $this->svc()->summary($t);
        // daftar ruang dalam sesi + progres
        $rooms = $t->items()->whereNotNull('expected_location_id')
            ->selectRaw("expected_location_id as id, count(*) as n, sum(result <> 'pending') as done")
            ->groupBy('expected_location_id')->get()->keyBy('id');
        $locs = Location::query()->whereIn('id', $rooms->keys())->orderBy('sort_key')->get(['id', 'name', 'full_path']);
        $room = $this->ruang && $rooms->has($this->ruang) ? $locs->firstWhere('id', $this->ruang) : null;
        $here = $room ? $t->items()->with('asset:id,asset_code,name,condition_grade', 'foundLocation:id,name')
            ->where(fn ($q) => $q->where('expected_location_id', $room->id)->orWhere('found_location_id', $room->id))
            ->orderByRaw("field(result, 'pending', 'moved', 'found', 'not_found', 'unregistered')")->orderBy('id')->get() : collect();

        $disc = ($this->tab === 'hasil' || $t->status !== 'counting' || ! $u->hasPermission('stocktake.count')) ? [
            'not_found' => $t->items()->with('asset:id,asset_code,name', 'expectedLocation:id,full_path,name')->where('result', 'not_found')->limit(300)->get(),
            'moved' => $t->items()->with('asset:id,asset_code,name', 'expectedLocation:id,full_path,name', 'foundLocation:id,full_path,name')->where('result', 'moved')->limit(300)->get(),
            'grade' => $t->items()->with('asset:id,asset_code,name')->whereIn('result', ['found', 'moved'])->whereNotNull('found_grade')->whereColumn('found_grade', '!=', 'expected_grade')->limit(300)->get(),
            'unregistered' => $t->items()->with('foundLocation:id,full_path,name', 'counter:id,name')->where('result', 'unregistered')->get(),
            'proposed' => Disposal::where('stock_take_id', $t->id)->whereNotIn('status', ['rejected', 'cancelled'])->pluck('asset_id')->flip(),
        ] : null;

        return $data + compact('sum', 'rooms', 'locs', 'room', 'here', 'disc');
    }
}
