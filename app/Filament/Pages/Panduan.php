<?php

namespace App\Filament\Pages;

use App\Support\Help;
use Filament\Pages\Page;

/** Daftar semua panduan layar; ?baca=kunci membuka satu panduan penuh (bisa dicetak / disimpan PDF). */
class Panduan extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?int $navigationSort = 99;

    protected static ?string $navigationLabel = 'Panduan';

    protected static ?string $title = 'Panduan penggunaan';

    protected static ?string $slug = 'panduan';

    protected string $view = 'filament.pages.panduan';

    public ?string $baca = null;

    public function mount(): void
    {
        $k = (string) request('baca');
        $this->baca = Help::has($k) ? $k : null;
    }

    public function getTitle(): string
    {
        return $this->baca ? 'Panduan · '.Help::get($this->baca)['title'] : 'Panduan penggunaan';
    }

    public function getIndexProperty(): array
    {
        return Help::index();
    }
}
