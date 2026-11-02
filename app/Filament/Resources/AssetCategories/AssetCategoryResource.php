<?php

namespace App\Filament\Resources\AssetCategories;

use App\Filament\Concerns\HasPermissions;
use App\Models\Asset;
use App\Models\AssetCategory;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class AssetCategoryResource extends Resource
{
    use HasPermissions;

    protected static ?string $model = AssetCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Kategori aset';

    protected static ?string $pluralModelLabel = 'Kategori aset';

    protected static ?string $viewPermission = 'reference.manage';

    protected static ?string $editPermission = 'reference.manage';

    public const GROUPS = ['kamar' => 'Kamar & koridor', 'mice' => 'MICE & ballroom', 'kitchen' => 'Kitchen & pastry', 'mep' => 'MEP & utilitas', 'publik' => 'Area publik', 'keselamatan' => 'Keselamatan', 'it' => 'IT & keamanan'];

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('code')->label('Kode')->required()->maxLength(30)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $r) => $r->where('property_id', app(\App\Support\CurrentProperty::class)->id())),
            TextInput::make('name')->label('Nama')->required(),
            Select::make('group')->label('Kelompok')->options(self::GROUPS)->native(false),
            Select::make('default_criticality')->label('Kritikalitas default')->options(Asset::CRITICALITY)->default('medium')->native(false),
            Select::make('tracking_mode')->label('Pelacakan')->options(['individual' => 'Per unit (kode & QR per unit)', 'quantity' => 'Kelompok kuantitas per lokasi'])->default('individual')->native(false),
            TextInput::make('default_pm_interval_days')->label('Interval PM usulan (hari)')->numeric()->minValue(1),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Kode')->searchable()->sortable(),
            TextColumn::make('name')->label('Nama')->searchable(),
            TextColumn::make('group')->label('Kelompok')->formatStateUsing(fn ($state) => self::GROUPS[$state] ?? $state)->badge()->color('gray'),
            TextColumn::make('default_criticality')->label('Kritikalitas')->formatStateUsing(fn ($state) => Asset::CRITICALITY[$state] ?? $state),
            TextColumn::make('tracking_mode')->label('Pelacakan')->formatStateUsing(fn ($state) => $state === 'quantity' ? 'Kuantitas' : 'Per unit'),
            TextColumn::make('assets_count')->label('Aset')->counts('assets'),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
        ])->filters([SelectFilter::make('group')->label('Kelompok')->options(self::GROUPS)])
            ->defaultSort('code')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAssetCategories::route('/')];
    }
}
