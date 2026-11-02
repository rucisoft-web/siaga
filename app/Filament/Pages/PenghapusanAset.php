<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\Disposal;
use App\Services\DisposalService;
use App\Services\WorkflowException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/** Penghapusan aset: usulan, persetujuan (pemisahan tugas), penyelesaian & berita acara. */
class PenghapusanAset extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box-x-mark';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengendalian Aset';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Penghapusan aset';

    protected static ?string $title = 'Penghapusan aset';

    protected static ?string $slug = 'penghapusan-aset';

    protected string $view = 'filament.pages.penghapusan-aset';

    #[Url]
    public string $status = 'aktif';

    public array $form = ['asset_code' => '', 'method' => 'scrap', 'reason' => '', 'justification' => '', 'proceeds' => null];

    public array $note = [];

    public array $done = [];

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return (bool) ($u?->hasPermission('disposal.request') || $u?->hasPermission('disposal.approve') || $u?->hasPermission('asset.view_financial'));
    }

    public function mount(): void
    {
        if ($code = request()->query('aset')) {
            $this->form['asset_code'] = (string) $code;
        }
    }

    protected function run(callable $fn, string $ok): void
    {
        try {
            $fn();
            Notification::make()->title($ok)->success()->send();
        } catch (WorkflowException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }

    public function propose(): void
    {
        $this->validate(['form.asset_code' => 'required|string', 'form.method' => 'required|in:'.implode(',', array_keys(Disposal::METHODS)),
            'form.reason' => 'required|string|max:250', 'form.justification' => 'nullable|string|max:2000', 'form.proceeds' => 'nullable|numeric|min:0']);
        $asset = Asset::where('asset_code', trim($this->form['asset_code']))->first();
        if (! $asset) {
            $this->addError('form.asset_code', 'Kode aset tidak ditemukan.');

            return;
        }
        $this->run(function () use ($asset) {
            app(DisposalService::class)->propose($asset, $this->form, auth()->user());
            $this->form = ['asset_code' => '', 'method' => 'scrap', 'reason' => '', 'justification' => '', 'proceeds' => null];
        }, 'Usulan penghapusan dibuat dan menunggu persetujuan.');
    }

    public function decide(int $id, bool $approve): void
    {
        $this->run(fn () => app(DisposalService::class)->decide(Disposal::findOrFail($id), $approve, $this->note[$id] ?? null, auth()->user()), $approve ? 'Usulan disetujui.' : 'Usulan ditolak.');
    }

    public function complete(int $id): void
    {
        $this->run(fn () => app(DisposalService::class)->complete(Disposal::findOrFail($id), array_filter($this->done[$id] ?? []), auth()->user()), 'Penghapusan selesai. Aset keluar dari daftar aset tetap.');
    }

    public function cancel(int $id): void
    {
        $this->run(fn () => app(DisposalService::class)->cancel(Disposal::findOrFail($id), auth()->user()), 'Usulan dibatalkan.');
    }

    protected function getViewData(): array
    {
        $u = auth()->user();
        $q = Disposal::with(['asset:id,asset_code,name,location_id', 'asset.location:id,full_path,name', 'requester:id,name', 'approver:id,name'])->latest();
        $q->when($this->status === 'aktif', fn ($q) => $q->whereIn('status', ['submitted', 'approved']))
            ->when($this->status === 'selesai', fn ($q) => $q->whereIn('status', ['completed', 'rejected', 'cancelled']));
        $all = Disposal::query()->selectRaw('status, count(*) n, sum(book_value) bv, sum(coalesce(proceeds,0)) pr')->groupBy('status')->get()->keyBy('status');

        return [
            'rows' => $q->limit(200)->get(), 'all' => $all, 'methods' => Disposal::METHODS, 'statuses' => Disposal::STATUSES,
            'canRequest' => $u->hasPermission('disposal.request'), 'canApprove' => $u->hasPermission('disposal.approve'),
            'fin' => $u->canSeeFinancials(), 'me' => $u->id, 'isAdmin' => $u->hasRole('super-admin'),
        ];
    }
}
