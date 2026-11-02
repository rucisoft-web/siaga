<?php

namespace App\Filament\Pages;

use App\Services\GroupService;
use Filament\Pages\Page;

/** Dasbor konsolidasi grup: perbandingan seluruh perusahaan untuk direksi grup. */
class DasborGrup extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static string|\UnitEnum|null $navigationGroup = 'Umum';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Dasbor grup';

    protected static ?string $title = 'Dasbor konsolidasi grup';

    protected static ?string $slug = 'dasbor-grup';

    protected string $view = 'filament.pages.dasbor-grup';

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return (bool) ($u && \App\Support\CurrentProperty::canSwitch($u) && $u->hasPermission('dashboard.view'));
    }

    public function refreshData(): void
    {
        app(GroupService::class)->companies(true);
    }

    protected function getViewData(): array
    {
        $rows = app(GroupService::class)->companies();

        return ['rows' => $rows, 'fin' => auth()->user()->canSeeFinancials()];
    }
}
