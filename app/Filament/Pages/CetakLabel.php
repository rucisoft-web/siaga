<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LabelPrintBatches\LabelPrintBatchResource;
use App\Filament\Support\F;
use App\Models\AssetCategory;
use App\Models\GenerationBatch;
use App\Models\Location;
use App\Services\LabelService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/** M03 Cetak label QR massal: filter → preview urutan & jumlah lembar → PDF ukuran aktual. */
class CetakLabel extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengendalian Aset';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Cetak label QR';

    protected static ?string $title = 'Cetak label QR';

    protected string $view = 'filament.pages.cetak-label';

    public array $roomIds = [];

    public ?int $floorId = null;

    public array $categoryIds = [];

    public ?int $generationBatchId = null;

    public string $assetIds = '';

    public bool $includeUnverified = false;

    public bool $includeGroups = true;

    public string $printState = 'all';

    public string $profile = 'a4-50x30';

    public int $startCell = 1;

    public int $copies = 1;

    public string $reason = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPermission('label.print');
    }

    public function mount(): void
    {
        $this->assetIds = (string) request('assets', '');
        if ($b = request()->integer('batch')) {
            $this->generationBatchId = $b;
            $this->includeUnverified = true;
            $this->printState = 'never_printed';
        }
        if ($this->assetIds !== '') {
            $this->includeUnverified = true;
        }
    }

    protected function filters(): array
    {
        return array_filter([
            'room_ids' => array_map('intval', $this->roomIds), 'floor_id' => $this->floorId, 'category_ids' => array_map('intval', $this->categoryIds),
            'generation_batch_id' => $this->generationBatchId, 'include_unverified' => $this->includeUnverified, 'include_groups' => $this->includeGroups,
            'print_state' => $this->printState, 'asset_ids' => array_values(array_filter(array_map('intval', explode(',', $this->assetIds)))),
        ], fn ($v) => $v !== null && $v !== [] && $v !== '');
    }

    public function getLabelsProperty(): Collection
    {
        return app(LabelService::class)->select($this->filters());
    }

    public function getLayoutProperty(): array
    {
        return app(LabelService::class)->layout($this->labels->count(), $this->profile, $this->startCell, $this->copies);
    }

    public function getRoomsProperty()
    {
        return Location::whereIn('location_type', ['room', 'venue', 'area'])->where('is_active', true)->orderBy('sort_key')->get();
    }

    public function getFloorsProperty()
    {
        return Location::whereIn('location_type', ['building', 'zone', 'floor'])->orderBy('sort_key')->get();
    }

    public function getCategoriesProperty()
    {
        return AssetCategory::where('is_active', true)->orderBy('name')->get();
    }

    public function getBatchesProperty()
    {
        return GenerationBatch::latest()->limit(30)->get();
    }

    public function resetFilters(): void
    {
        $this->reset(['roomIds', 'floorId', 'categoryIds', 'generationBatchId', 'assetIds', 'includeUnverified', 'printState']);
    }

    public function generate(): void
    {
        $labels = $this->labels;
        $reprint = $labels->contains(fn ($l) => $l->print_items_count > 0);
        $batch = null;
        F::attempt(function () use ($labels, $reprint, &$batch) {
            $batch = app(LabelService::class)->createBatch($labels, $this->profile, $this->startCell, auth()->user(), $this->filters(),
                $reprint ? 'reprint' : 'new', $reprint ? trim($this->reason) : null, max(1, min(5, $this->copies)));
        });
        if ($batch) {
            \Filament\Notifications\Notification::make()->title('Batch cetak '.$batch->number.' siap')
                ->body($batch->label_count.' label · '.$batch->sheet_count.' lembar. Cetak pada skala 100% (bukan "fit to page").')->success()->send();
            $this->redirect(LabelPrintBatchResource::getUrl('view', ['record' => $batch]));
        }
    }
}
