<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLabel;
use App\Models\Location;
use App\Models\RoomSurvey;
use App\Services\SurveyService;
use App\Services\WorkflowException;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Session;
use Livewire\WithFileUploads;
use App\Models\AssetQuantityGroup;

/**
 * Pendataan aset awal untuk petugas keliling (tampilan HP).
 * Pilih kamar → pindai label yang ditempel → catat kondisi / foto → "Tidak ada" / "Aset tambahan" → Selesai kamar.
 */
class PendataanAset extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengendalian Aset';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Pendataan aset';

    protected static ?string $title = 'Pendataan aset';

    protected static ?string $slug = 'pendataan';

    protected string $view = 'filament.pages.pendataan-aset';

    public string $tab = 'kamar';

    public ?int $roomId = null;

    public ?int $floorId = null;

    public string $search = '';

    public string $filter = 'belum';

    public ?int $focusId = null;

    public ?int $blankLabelId = null;

    public array $feed = [];

    /** formulir item terbuka */
    public ?string $cond = null;

    public string $brand = '';

    public string $model = '';

    public string $serial = '';

    public $photo = null;

    /** aset tambahan */
    public bool $showExtra = false;

    public array $extra = ['name' => '', 'category_id' => null, 'condition_grade' => 'A', 'brand' => '', 'serial_number' => ''];

    public $extraPhoto = null;

    /** ukuran label printer portable, diingat per sesi login */
    #[Session]
    public string $printProfile = '';

    public ?string $printUrl = null;

    public ?int $printCount = null;

    /** item hitungan: [group_id => jumlah] */
    public array $counts = [];

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return (bool) ($u?->hasPermission('survey.run') || $u?->hasPermission('dashboard.view'));
    }

    public function mount(): void
    {
        $this->roomId = request()->integer('room') ?: null;
        $this->tab = request('tab') === 'progres' ? 'progres' : 'kamar';
        if (! auth()->user()->hasPermission('survey.run')) {
            $this->tab = 'progres';
        }
        if (! config('assetcare.label_profiles.'.$this->printProfile)) {
            $this->printProfile = (string) config('assetcare.survey_print_profile', 'roll-50x30');
        }
    }

    public function getPrintProfilesProperty(): array
    {
        return collect(config('assetcare.label_profiles'))->sortBy(fn ($p) => $p['type'] === 'roll' ? 0 : 1)->map(fn ($p) => $p['name'])->all();
    }

    public function getGroupsProperty(): Collection
    {
        return $this->room ? $this->svc()->groups($this->room) : collect();
    }

    public function getUnprintedProperty(): int
    {
        return $this->room ? $this->svc()->unprintedCount($this->room) : 0;
    }

    /** Cetak semua label kamar ini yang belum pernah dicetak (atau satu aset bila $assetId diisi). */
    public function printRoom(?int $assetId = null): void
    {
        $this->run(function () use ($assetId) {
            $b = $this->svc()->printLabels($this->room, auth()->user(), $this->printProfile, $assetId ? [$assetId] : null);
            $this->printUrl = route('labels.print', $b);
            $this->printCount = $b->label_count;
            $this->push('info', $b->label_count.' label siap dicetak ('.$b->number.'). Ketuk "Buka & cetak", pilih printer label.');
        });
    }

    public function saveCount(int $groupId): void
    {
        $this->run(function () use ($groupId) {
            $g = AssetQuantityGroup::where('location_id', $this->roomId)->findOrFail($groupId);
            $v = $this->counts[$groupId] ?? null;
            if ($v === null || $v === '' || ! is_numeric($v)) {
                throw WorkflowException::invalid('Isi jumlah fisik '.$g->name.'.');
            }
            $g = $this->svc()->countGroup($g, (float) $v, auth()->user());
            $this->push('success', \Illuminate\Support\Str::before($g->name, ' · ').': '.rtrim(rtrim(number_format((float) $g->on_hand, 2, ',', ''), '0'), ',').' '.$g->unit.' dicatat'.($g->expected_qty !== null && (float) $g->expected_qty !== (float) $g->on_hand ? ' (standar '.(float) $g->expected_qty.').' : '.'));
        });
    }

    public function getTitle(): string
    {
        return $this->room ? 'Pendataan · '.$this->room->labelText() : 'Pendataan aset';
    }

    protected function svc(): SurveyService
    {
        return app(SurveyService::class);
    }

    public function getCanRunProperty(): bool
    {
        return (bool) auth()->user()?->hasPermission('survey.run');
    }

    // ---------- daftar kamar
    public function getFloorsProperty(): Collection
    {
        return Location::whereIn('location_type', ['floor', 'zone', 'building'])->where('is_active', true)->orderBy('sort_key')->get();
    }

    public function getRoomListProperty(): Collection
    {
        $q = Location::whereIn('location_type', ['room', 'venue', 'area'])->where('is_active', true)->orderBy('sort_key');
        if ($this->floorId && ($f = Location::find($this->floorId))) {
            $q->where('sort_key', 'like', $f->sort_key.'/%');
        }
        if (($s = trim($this->search)) !== '') {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('room_ref', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }
        $rooms = $q->with('roomType')->limit(400)->get();
        $surveys = RoomSurvey::whereIn('location_id', $rooms->pluck('id'))->with('surveyor')->get()->keyBy('location_id');
        $counts = Asset::whereIn('location_id', $rooms->pluck('id'))->whereNotIn('lifecycle_status', ['disposed', 'lost', 'retired'])
            ->selectRaw("location_id, count(*) as total, sum(case when verification_status <> 'unverified' then 1 else 0 end) as checked")
            ->groupBy('location_id')->get()->keyBy('location_id');

        return $rooms->map(fn ($r) => (object) [
            'room' => $r, 'survey' => $surveys->get($r->id),
            'total' => (int) ($counts->get($r->id)->total ?? 0), 'checked' => (int) ($counts->get($r->id)->checked ?? 0),
        ])->filter(fn ($x) => match ($this->filter) {
            'belum' => $x->survey?->status !== 'done',
            'selesai' => $x->survey?->status === 'done',
            default => true,
        })->values();
    }

    public function openRoom(int $id): void
    {
        $this->roomId = $id;
        $this->resetItemForm();
        $this->focusId = null;
        $this->blankLabelId = null;
        $this->feed = [];
        $this->showExtra = false;
        $this->printUrl = null;
        $this->counts = AssetQuantityGroup::where('location_id', $id)->pluck('on_hand', 'id')->map(fn ($v) => $v === null ? '' : (float) $v)->all();
    }

    public function backToList(): void
    {
        $this->roomId = null;
        $this->focusId = null;
        $this->blankLabelId = null;
    }

    // ---------- kamar terpilih
    public function getRoomProperty(): ?Location
    {
        return $this->roomId ? Location::with('roomType')->find($this->roomId) : null;
    }

    public function getItemsProperty(): Collection
    {
        return $this->room ? $this->svc()->items($this->room) : collect();
    }

    public function getSurveyProperty(): ?RoomSurvey
    {
        return $this->roomId ? RoomSurvey::where('location_id', $this->roomId)->first() : null;
    }

    public function getBlankLabelProperty(): ?AssetLabel
    {
        return $this->blankLabelId ? AssetLabel::find($this->blankLabelId) : null;
    }

    public function getCategoriesProperty(): array
    {
        return AssetCategory::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
    }

    public function getNextRoomProperty(): ?Location
    {
        if (! $this->room) {
            return null;
        }
        $done = RoomSurvey::where('status', 'done')->pluck('location_id');

        return Location::whereIn('location_type', ['room', 'venue', 'area'])->where('is_active', true)
            ->where('sort_key', '>', $this->room->sort_key)->whereNotIn('id', $done)
            ->whereHas('assets')->orderBy('sort_key')->first();
    }

    public function state(Asset $a): string
    {
        return $this->svc()->itemState($a);
    }

    /** dipanggil komponen pemindai */
    public function scan(string $text, string $channel = 'scanner'): void
    {
        if (! $this->room) {
            $this->push('warning', 'Pilih kamar/ruang dulu.');

            return;
        }
        $this->run(function () use ($text) {
            $r = $this->svc()->scan($this->room, $text, auth()->user());
            if (! empty($r['blank_label_id'])) {
                $this->blankLabelId = $r['blank_label_id'];
            }
            if (! empty($r['asset_id']) && in_array($r['level'], ['success', 'info', 'warning'], true) && ! str_contains($r['message'], 'bukan')) {
                $this->focus($r['asset_id']);
            }
            $this->push($r['level'], $r['message']);
        });
    }

    public function useBlankFor(int $assetId): void
    {
        $this->run(function () use ($assetId) {
            $asset = Asset::where('location_id', $this->roomId)->findOrFail($assetId);
            $r = $this->svc()->attachBlank($this->room, $this->blankLabel, $asset, auth()->user());
            $this->blankLabelId = null;
            $this->focus($assetId);
            $this->push($r['level'], $r['message']);
        });
    }

    public function cancelBlank(): void
    {
        $this->blankLabelId = null;
    }

    public function focus(?int $assetId): void
    {
        $this->focusId = $this->focusId === $assetId ? null : $assetId;
        $this->resetItemForm();
        if ($this->focusId && ($a = Asset::find($this->focusId))) {
            $this->cond = $a->condition_grade;
            $this->brand = (string) $a->brand;
            $this->model = (string) $a->model;
            $this->serial = (string) $a->serial_number;
        }
    }

    public function saveItem(): void
    {
        $this->validate(['photo' => 'nullable|image|max:8192']);
        $this->run(function () {
            $a = Asset::where('location_id', $this->roomId)->findOrFail($this->focusId);
            $this->svc()->record($a, true, $this->cond, ['brand' => $this->brand, 'model' => $this->model, 'serial_number' => $this->serial], auth()->user(), $this->photo);
            $this->push('success', $a->asset_code.' · '.$a->name.' — kondisi '.$this->cond.' dicatat.');
            $this->focusNextOpen($a->id);
        });
    }

    public function markMissing(int $assetId): void
    {
        $this->run(function () use ($assetId) {
            $a = Asset::where('location_id', $this->roomId)->findOrFail($assetId);
            $this->svc()->record($a, false, null, ['note' => 'Tidak ditemukan saat pendataan'], auth()->user());
            $this->push('warning', $a->asset_code.' · '.$a->name.' ditandai TIDAK ADA.');
            $this->focusNextOpen($a->id);
        });
    }

    public function addExtra(): void
    {
        $this->validate(['extraPhoto' => 'nullable|image|max:8192', 'extra.name' => 'required|max:200', 'extra.category_id' => 'required|integer']);
        $this->run(function () {
            $a = $this->svc()->addExtra($this->room, $this->extra, auth()->user(), $this->blankLabel, $this->extraPhoto);
            $this->push('success', 'Aset tambahan '.$a->asset_code.' · '.$a->name.' dibuat'.($this->blankLabel ? ' & label stok dihubungkan.' : '. Labelnya dicetak nanti (Cetak label QR › belum pernah dicetak).'));
            $this->blankLabelId = null;
            $this->showExtra = false;
            $this->extra = ['name' => '', 'category_id' => $this->extra['category_id'], 'condition_grade' => 'A', 'brand' => '', 'serial_number' => ''];
            $this->extraPhoto = null;
        });
    }

    public function finishRoom(): void
    {
        $this->run(function () {
            $s = $this->svc()->finish($this->room, auth()->user());
            $this->push('success', $this->room->labelText().' selesai: '.$s->found_count.' ada, '.$s->missing_count.' tidak ada, '.$s->extra_count.' tambahan.');
        });
    }

    public function reopenRoom(): void
    {
        $this->run(fn () => $this->svc()->reopen($this->room, auth()->user()));
    }

    public function goNext(): void
    {
        if ($n = $this->nextRoom) {
            $this->openRoom($n->id);
        }
    }

    public function getProgressProperty(): array
    {
        return $this->svc()->progress();
    }

    protected function focusNextOpen(int $afterId): void
    {
        $this->resetItemForm();
        $items = $this->svc()->items($this->room);
        $idx = $items->search(fn ($a) => $a->id === $afterId);
        $next = $items->slice(($idx === false ? -1 : $idx) + 1)->first(fn ($a) => in_array($this->state($a), ['todo', 'labeled'], true))
            ?? $items->first(fn ($a) => in_array($this->state($a), ['todo', 'labeled'], true));
        $this->focusId = null;
        if ($next) {
            $this->focus($next->id);
        }
    }

    protected function resetItemForm(): void
    {
        $this->cond = null;
        $this->brand = $this->model = $this->serial = '';
        $this->photo = null;
    }

    protected function run(callable $fn): void
    {
        try {
            $fn();
        } catch (WorkflowException $e) {
            $this->push('danger', $e->getMessage());
        } catch (ValidationException $e) {
            $this->push('danger', collect($e->errors())->flatten()->join(' '));
        }
    }

    protected function push(string $level, string $message): void
    {
        array_unshift($this->feed, ['level' => $level, 'message' => $message, 'at' => now()->timezone(\App\Support\Fmt::tz())->format('H:i:s')]);
        $this->feed = array_slice($this->feed, 0, 6);
    }
}
