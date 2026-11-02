<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Filament\Support\F;
use App\Models\WorkOrder;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'workOrders';

    protected static ?string $title = 'Work order';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Nomor')->fontFamily('mono'),
                F::priorityColumn(),
                TextColumn::make('title')->label('Pekerjaan')->wrap(),
                TextColumn::make('failure_mode')->label('Mode kegagalan')->placeholder('—'),
                F::statusColumn('status', WorkOrder::STATUSES, WorkOrder::STATUS_COLORS),
                TextColumn::make('received_at')->label('Diterima')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('restored_at')->label('Pulih')->dateTime('d M Y H:i')->placeholder('—'),
            ])
            ->defaultSort('received_at', 'desc')
            ->recordActions([Action::make('buka')->label('Buka')->url(fn ($record) => WorkOrderResource::getUrl('view', ['record' => $record]))]);
    }
}
