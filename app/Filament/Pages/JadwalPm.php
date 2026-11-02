<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\PmSchedule;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Services\PmService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/** Jadwal PM, inspeksi & kalibrasi; WO dibuat otomatis tiap hari, atau manual lewat tombol. */
class JadwalPm extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Pemeliharaan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Jadwal PM & kalibrasi';

    protected static ?string $title = 'Jadwal PM, inspeksi & kalibrasi';

    protected static ?string $slug = 'jadwal-pm';

    protected string $view = 'filament.pages.jadwal-pm';

    #[Url]
    public string $tab = 'jadwal';

    public ?int $editing = null;

    public array $form = [];

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return (bool) ($u?->hasPermission('pm.manage') || $u?->hasPermission('wo.view'));
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editing = null;
        $this->form = ['title' => '', 'type' => 'preventive', 'target' => 'asset', 'asset_code' => '', 'category_id' => '', 'location_id' => '',
            'interval_days' => 30, 'next_due' => PmService::today()->addDays(7)->toDateString(), 'lead_days' => 3, 'priority' => 'P4',
            'pic_id' => '', 'vendor_id' => '', 'regulation' => '', 'checklist' => '', 'estimated_hours' => ''];
    }

    protected function guard(): bool
    {
        if (! auth()->user()->hasPermission('pm.manage')) {
            Notification::make()->title('Anda tidak memiliki izin mengelola jadwal PM.')->danger()->send();

            return false;
        }

        return true;
    }

    public function edit(int $id): void
    {
        $s = PmSchedule::with('asset')->findOrFail($id);
        $this->editing = $s->id;
        $this->form = [
            'title' => $s->title, 'type' => $s->type, 'target' => $s->asset_id ? 'asset' : 'group', 'asset_code' => $s->asset?->asset_code ?? '',
            'category_id' => (string) $s->category_id, 'location_id' => (string) $s->location_id, 'interval_days' => $s->interval_days,
            'next_due' => $s->next_due->toDateString(), 'lead_days' => $s->lead_days, 'priority' => $s->priority, 'pic_id' => (string) $s->pic_id,
            'vendor_id' => (string) $s->vendor_id, 'regulation' => $s->regulation, 'checklist' => $s->checklist, 'estimated_hours' => $s->estimated_hours,
        ];
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        if (! $this->guard()) {
            return;
        }
        $this->validate([
            'form.title' => 'required|string|max:200', 'form.type' => 'required|in:'.implode(',', array_keys(PmSchedule::TYPES)),
            'form.target' => 'required|in:asset,group', 'form.interval_days' => 'required|integer|min:1|max:3650', 'form.next_due' => 'required|date',
            'form.lead_days' => 'required|integer|min:0|max:60', 'form.priority' => 'required|in:P1,P2,P3,P4', 'form.checklist' => 'nullable|string|max:4000',
            'form.regulation' => 'nullable|string|max:250', 'form.estimated_hours' => 'nullable|numeric|min:0|max:999',
        ]);
        $f = $this->form;
        $asset = null;
        if ($f['target'] === 'asset') {
            $asset = Asset::where('asset_code', trim($f['asset_code']))->first();
            if (! $asset) {
                $this->addError('form.asset_code', 'Kode aset tidak ditemukan.');

                return;
            }
        } elseif (! $f['category_id'] && ! $f['location_id']) {
            $this->addError('form.category_id', 'Pilih kategori dan/atau lokasi.');

            return;
        }
        $data = [
            'title' => $f['title'], 'type' => $f['type'], 'asset_id' => $asset?->id,
            'category_id' => $asset ? null : ($f['category_id'] ?: null), 'location_id' => $asset ? null : ($f['location_id'] ?: null),
            'interval_days' => (int) $f['interval_days'], 'next_due' => $f['next_due'], 'lead_days' => (int) $f['lead_days'], 'priority' => $f['priority'],
            'pic_id' => $f['pic_id'] ?: null, 'vendor_id' => $f['vendor_id'] ?: null, 'regulation' => $f['regulation'] ?: null,
            'checklist' => $f['checklist'] ?: null, 'estimated_hours' => $f['estimated_hours'] !== '' && $f['estimated_hours'] !== null ? $f['estimated_hours'] : null,
        ];
        if ($this->editing) {
            PmSchedule::findOrFail($this->editing)->update($data);
            Notification::make()->title('Jadwal diperbarui.')->success()->send();
        } else {
            $s = PmSchedule::create($data + ['is_active' => true]);
            Notification::make()->title('Jadwal '.$s->code.' dibuat. WO akan dibuat otomatis '.$s->lead_days.' hari sebelum jatuh tempo.')->success()->send();
        }
        $this->resetForm();
    }

    public function generateNow(int $id): void
    {
        if (! $this->guard()) {
            return;
        }
        $wo = app(PmService::class)->generate(PmSchedule::findOrFail($id), auth()->user());
        $wo
            ? Notification::make()->title('Work order '.$wo->number.' dibuat. Jatuh tempo berikutnya sudah dimajukan.')->success()->send()
            : Notification::make()->title('Work order untuk jatuh tempo ini sudah ada.')->warning()->send();
    }

    public function toggle(int $id): void
    {
        if (! $this->guard()) {
            return;
        }
        $s = PmSchedule::findOrFail($id);
        $s->update(['is_active' => ! $s->is_active]);
        Notification::make()->title($s->is_active ? 'Jadwal diaktifkan.' : 'Jadwal dinonaktifkan.')->success()->send();
    }

    protected function getViewData(): array
    {
        $today = PmService::today();
        $schedules = PmSchedule::with(['asset:id,asset_code,name', 'category:id,name', 'location:id,name,full_path', 'pic:id,name', 'vendor:id,name', 'lastWorkOrder:id,number,status'])
            ->orderByDesc('is_active')->orderBy('next_due')->get();
        $open = WorkOrder::with(['asset:id,asset_code,name', 'location:id,name', 'pic:id,name', 'pmSchedule:id,code'])
            ->whereNotNull('pm_schedule_id')->whereIn('status', WorkOrder::OPEN)->orderBy('pm_due_date')->get();
        $from = $today->subDays(90)->toDateString();
        $past = WorkOrder::whereNotNull('pm_schedule_id')->whereBetween('pm_due_date', [$from, $today->toDateString()])->get(['id', 'status', 'due_at', 'completed_at', 'restored_at']);
        $onTime = $past->filter(fn ($w) => ($done = $w->completed_at ?? $w->restored_at) && $done <= $w->due_at)->count();
        $expiring = app(PmService::class)->expiring(60);
        $active = $schedules->where('is_active', true);

        return [
            'today' => $today, 'schedules' => $schedules, 'open' => $open, 'expiring' => $expiring,
            'kpi' => [
                'active' => $active->count(),
                'due7' => $active->filter(fn ($s) => $s->next_due->toDateString() <= $today->addDays(7)->toDateString())->count(),
                'open' => $open->count(),
                'late' => $open->filter(fn ($w) => $w->due_at && $w->due_at->isPast())->count(),
                'compliance' => $past->count() ? round($onTime / $past->count() * 100) : null, 'pastN' => $past->count(),
                'expiring' => $expiring->where('days', '>=', 0)->where('days', '<=', 30)->count(),
            ],
            'types' => PmSchedule::TYPES, 'intervals' => PmSchedule::INTERVALS,
            'categories' => AssetCategory::orderBy('name')->pluck('name', 'id'), 'locations' => Location::options(),
            'pics' => User::where('is_active', true)->orderBy('name')->get()->filter(fn ($u) => $u->hasPermission('wo.execute'))->pluck('name', 'id'),
            'vendors' => Vendor::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'canManage' => auth()->user()->hasPermission('pm.manage'),
            'woUrl' => fn ($w) => WorkOrderResource::getUrl('view', ['record' => $w]),
            'assetUrl' => fn ($id) => AssetResource::getUrl('view', ['record' => $id]),
        ];
    }
}
