<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\Property;
use App\Support\Branding;
use App\Support\CurrentProperty;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Pengaturan › Profil hotel: identitas & tampilan aplikasi.
 * Inilah yang membuat satu produk SIAGA bisa dipakai hotel mana saja tanpa mengubah kode.
 */
class ProfilHotel extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Profil hotel';

    protected static ?string $title = 'Profil hotel';

    protected static ?string $slug = 'profil-hotel';

    protected string $view = 'filament.pages.profil-hotel';

    public ?array $data = [];

    public const PALETTES = [
        'navy' => ['Navy & merah (bawaan)', '#1F3A5F', '#C8102E'],
        'charcoal' => ['Arang & emas', '#2B2B2B', '#B8892B'],
        'teal' => ['Teal & oranye', '#0F5257', '#E07A1F'],
        'forest' => ['Hijau tua & emas', '#1F4D3A', '#C9A227'],
        'maroon' => ['Marun & emas', '#5C1A1B', '#C9A227'],
        'royal' => ['Biru & kuning', '#1D4E89', '#F2B705'],
    ];

    public static function getNavigationLabel(): string
    {
        return istilah('Profil hotel');
    }

    public function getTitle(): string
    {
        return istilah('Profil hotel');
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPermission('settings.manage');
    }

    protected function property(): Property
    {
        return app(CurrentProperty::class)->get() ?? Property::orderBy('id')->firstOrFail();
    }

    public function mount(): void
    {
        $this->form->fill($this->property()->only([
            'name', 'legal_name', 'business_type', 'code', 'address', 'city', 'phone', 'email', 'star_rating', 'room_count',
            'app_name', 'tagline', 'logo_path', 'logo_dark_path', 'primary_color', 'accent_color', 'timezone',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        $hasAssets = fn () => Asset::withoutGlobalScopes()->where('property_id', $this->property()->id)->exists();

        return $schema->statePath('data')->components([
            Section::make(istilah('Identitas hotel'))->columns(2)->schema([
                TextInput::make('name')->label(istilah('Nama hotel'))->required()->maxLength(120),
                TextInput::make('legal_name')->label('Nama badan usaha (PT/CV)')->maxLength(150),
                Select::make('business_type')->label('Jenis usaha')->options(\App\Support\Terms::TYPES)->required()->native(false)->live()
                    ->helperText('Menentukan istilah di layar, laporan & label: kamar (hotel/RS), outlet (ritel), ruang (umum).'),
                TextInput::make('code')->label('Kode properti')->required()->maxLength(8)
                    ->regex('/^[A-Z0-9]{2,8}$/')->validationMessages(['regex' => 'Huruf kapital/angka, 2–8 karakter.'])
                    ->helperText(fn () => $hasAssets()
                        ? 'Terkunci: sudah ada aset bernomor & label tercetak dengan prefiks ini.'
                        : 'Prefiks kode aset, mis. HTL → HTL-AST-000001. Tidak bisa diubah setelah aset pertama dibuat.')
                    ->disabled($hasAssets)->dehydrated(fn () => ! $hasAssets()),
                Select::make('star_rating')->label('Kelas')->options([
                    '1' => 'Bintang 1', '2' => 'Bintang 2', '3' => 'Bintang 3', '4' => 'Bintang 4', '5' => 'Bintang 5',
                    'melati' => 'Melati', 'resort' => 'Resort / villa', 'lain' => 'Lainnya',
                ])->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => ($get('business_type') ?? 'hotel') === 'hotel'),
                TextInput::make('address')->label('Alamat')->columnSpanFull()->maxLength(255),
                TextInput::make('city')->label('Kota')->maxLength(80),
                TextInput::make('room_count')->label(istilah('Jumlah kamar'))->numeric()->minValue(0)->maxValue(10000),
                TextInput::make('phone')->label('Telepon')->maxLength(40),
                TextInput::make('email')->label('Email engineering / umum')->email()->maxLength(255),
                Select::make('timezone')->label('Zona waktu')->required()->options([
                    'Asia/Jakarta' => 'WIB — Asia/Jakarta', 'Asia/Makassar' => 'WITA — Asia/Makassar', 'Asia/Jayapura' => 'WIT — Asia/Jayapura',
                    'Asia/Singapore' => 'Asia/Singapore', 'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur', 'Asia/Bangkok' => 'Asia/Bangkok', 'UTC' => 'UTC',
                ])->helperText('Tanggal operasional, SLA & laporan memakai zona ini. Data tetap disimpan UTC.'),
            ]),
            Section::make('Tampilan aplikasi')->columns(2)->schema([
                TextInput::make('app_name')->label('Nama aplikasi')->placeholder('SIAGA')->maxLength(60),
                TextInput::make('tagline')->label('Tagline')->placeholder('Sistem Informasi Aset & Gangguan')->maxLength(120),
                Select::make('palette')->label('Pilih palet cepat')->dehydrated(false)->live()
                    ->options(collect(self::PALETTES)->map(fn ($p) => $p[0])->all())
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($p = self::PALETTES[$state] ?? null) {
                            $set('primary_color', $p[1]);
                            $set('accent_color', $p[2]);
                        }
                    })->columnSpanFull(),
                ColorPicker::make('primary_color')->label('Warna utama')->required()->regex('/^#[0-9A-Fa-f]{6}$/')
                    ->helperText('Tombol, tautan, garis atas kartu.'),
                ColorPicker::make('accent_color')->label('Warna aksen garis')->required()->regex('/^#[0-9A-Fa-f]{6}$/')
                    ->helperText('Garis tipis di bawah topbar, judul & tab aktif.'),
                FileUpload::make('logo_path')->label('Logo (latar terang)')->image()->disk('local')->directory('brand')->visibility('private')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->maxSize(1024)
                    ->helperText('PNG/JPG/WebP transparan, maks. 1 MB, ±400 × 120 px. Kosong = logo teks bergaris.'),
                FileUpload::make('logo_dark_path')->label('Logo (mode gelap, opsional)')->image()->disk('local')->directory('brand')->visibility('private')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->maxSize(1024),
            ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $data['primary_color'] = strtoupper($data['primary_color']);
        $data['accent_color'] = strtoupper($data['accent_color']);
        $p = $this->property();
        $p->auditReason = 'Profil hotel diperbarui';
        $p->fill($data)->save();
        Branding::flush();

        Notification::make()->success()->title(istilah('Profil hotel disimpan'))->body('Muat ulang halaman untuk melihat logo & warna baru.')->send();
        $this->redirect(static::getUrl());
    }
}
