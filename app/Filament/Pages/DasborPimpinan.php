<?php

namespace App\Filament\Pages;

use App\Services\LeadershipService;
use App\Support\CurrentProperty;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/** Dasbor pimpinan: grafik kondisi & risiko aset, kinerja perbaikan, biaya & dampak — untuk keputusan perbaikan / penggantian. */
class DasborPimpinan extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Umum';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Dasbor pimpinan';

    protected static ?string $title = 'Dasbor pimpinan';

    protected static ?string $slug = 'dasbor-pimpinan';

    protected string $view = 'filament.pages.dasbor-pimpinan';

    #[Url(as: 'lokasi')]
    public ?int $lokasi = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPermission('dashboard.view');
    }

    protected function getViewData(): array
    {
        $svc = new LeadershipService;
        $buildings = $svc->buildings();
        $lokasi = isset($buildings[$this->lokasi]) ? $this->lokasi : null;

        return [
            'buildings' => $buildings,
            'lokasi' => $lokasi,
            'd' => $svc->data($lokasi),
            'financial' => auth()->user()->canSeeFinancials(),
            'property' => app(CurrentProperty::class)->get(),
        ];
    }
}
