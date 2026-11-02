<?php

namespace App\Filament\Pages;

use App\Services\MasterImportService;
use App\Services\WorkflowException;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

/**
 * Impor workbook master aset (format GPCH) sebagai bekal pendataan:
 * tipe kamar, template isi kamar, ruang meeting, area umum & item estimasi, dan (opsional) nomor kamar.
 */
class ImporMaster extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengendalian Aset';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Impor master aset';

    protected static ?string $title = 'Impor master aset (Excel)';

    protected static ?string $slug = 'impor-master';

    protected string $view = 'filament.pages.impor-master';

    public $file = null;

    public bool $publish = false;

    public ?array $result = null;

    public ?string $error = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPermission('template.manage');
    }

    public function check(): void
    {
        $this->runImport(true);
    }

    public function import(): void
    {
        $this->runImport(false);
    }

    protected function runImport(bool $dry): void
    {
        $this->error = null;
        $this->validate(['file' => 'required|file|max:20480|mimes:xlsx'], ['file.required' => 'Pilih file Excel (.xlsx) dulu.', 'file.mimes' => 'File harus .xlsx.']);
        try {
            $this->result = app(MasterImportService::class)->import($this->file->getRealPath(), auth()->user(), $dry, $this->publish);
        } catch (WorkflowException $e) {
            $this->error = $e->getMessage();
            $this->result = null;
        } catch (\Throwable $e) {
            report($e);
            $this->error = 'File tidak dapat dibaca: '.$e->getMessage();
            $this->result = null;
        }
    }

    public function getLabelsProperty(): array
    {
        return MasterImportService::LABELS;
    }
}
