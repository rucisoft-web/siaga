<?php

namespace App\Filament\Pages;

use App\Models\AssetLabel;
use App\Models\LabelPrintBatch;
use App\Services\LabelService;
use App\Services\WorkflowException;
use Filament\Pages\Page;

/**
 * Stok label QR kosong: dicetak sebelum turun ke lapangan (A4 atau printer roll),
 * lalu dihubungkan ke aset saat ditempel lewat Pendataan aset.
 */
class StokLabel extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengendalian Aset';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Stok label QR kosong';

    protected static ?string $title = 'Stok label QR kosong';

    protected static ?string $slug = 'stok-label';

    protected string $view = 'filament.pages.stok-label';

    public int $count = 27;

    public string $profile = 'a4-50x30';

    public int $startCell = 1;

    public ?int $batchId = null;

    public ?string $error = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPermission('label.print');
    }

    public function create(): void
    {
        $this->error = null;
        try {
            $batch = app(LabelService::class)->issueBlankBatch($this->count, $this->profile, $this->startCell, auth()->user());
            $this->batchId = $batch->id;
        } catch (WorkflowException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getStatsProperty(): array
    {
        $q = AssetLabel::whereNotNull('stock_code');

        return [
            'free' => (clone $q)->where('status', 'unassigned')->count(),
            'bound' => (clone $q)->where('status', 'active')->count(),
            'revoked' => (clone $q)->where('status', 'revoked')->count(),
        ];
    }

    public function getBatchesProperty()
    {
        return LabelPrintBatch::whereNotNull('filters->stok_kosong')->latest('id')->limit(10)->get();
    }

    public function getLayoutProperty(): array
    {
        return app(LabelService::class)->layout(max(1, $this->count), $this->profile, $this->startCell);
    }
}
