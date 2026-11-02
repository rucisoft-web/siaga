<?php

namespace App\Filament\Resources\GenerationBatches\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResultsRelationManager extends RelationManager
{
    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return istilah(parent::getTitle($ownerRecord, $pageClass));
    }

    protected static string $relationship = 'results';

    protected static ?string $title = 'Hasil per kamar';

    public function table(Table $table): Table
    {
        $labels = ['ok' => 'Berhasil', 'failed' => 'Gagal', 'conflict' => 'Konflik', 'skipped' => 'Dilewati'];

        return $table
            ->columns([
                TextColumn::make('room.full_path')->label(istilah('Kamar'))->wrap(),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => $labels[$state] ?? $state)
                    ->color(fn ($state) => ['ok' => 'success', 'failed' => 'danger', 'conflict' => 'warning'][$state] ?? 'gray'),
                TextColumn::make('created_count')->label('Dibuat'),
                TextColumn::make('matched_count')->label('Dicocokkan'),
                TextColumn::make('skipped_count')->label('Dilewati'),
                TextColumn::make('message')->label('Pesan')->wrap()->placeholder('—'),
            ]);
    }
}
