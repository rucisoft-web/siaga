<?php

namespace App\Filament\Pages;

use App\Support\Help;
use Filament\Pages\Page;

/** Rencana implementasi pendataan aset (lingkup, jadwal, sumber daya, QC, organisasi, komunikasi, risiko, pengadaan). */
class PanduanImplementasi extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static string|\UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Panduan implementasi';

    protected static ?string $title = 'Panduan implementasi';

    protected static ?string $slug = 'implementasi';

    protected string $view = 'filament.pages.panduan-implementasi';

    public function getGuideProperty(): array
    {
        return Help::get('implementasi');
    }

    /** daftar isi dari judul bagian (## …) */
    public function getTocProperty(): array
    {
        preg_match_all('/^## (.+)$/m', (string) file_get_contents(Help::path('implementasi')), $m);

        return $m[1];
    }
}
