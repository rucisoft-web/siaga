<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Beranda;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Support\Branding;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/** Panel /admin, tema "Garis" ala property management system. Identitas & warna dari Pengaturan › Profil hotel. */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile(EditProfile::class, isSimple: false)
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable()->brandName(config('assetcare.brand_name')),
            ])
            ->brandName(fn () => Branding::appName().' · '.Branding::hotelName())
            ->brandLogo(fn () => Branding::panelLogo())
            ->darkModeBrandLogo(fn () => Branding::panelLogo(dark: true))
            ->brandLogoHeight('2.4rem')
            ->favicon(asset('favicon.png'))
            ->colors(fn () => [
                'primary' => Color::generateV3Palette(Branding::primary()),
                'danger' => Color::generateV3Palette('#B42318'),
                'warning' => Color::generateV3Palette('#B54708'),
                'success' => Color::generateV3Palette('#1E7B45'),
                'info' => Color::generateV3Palette('#1D4E89'),
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->defaultAvatarProvider(\App\Filament\Support\InitialsAvatarProvider::class)
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->globalSearch(false)
            ->navigationGroups([
                NavigationGroup::make('Umum'),
                NavigationGroup::make('Pemeliharaan'),
                NavigationGroup::make('Aset'),
                NavigationGroup::make('Pengendalian Aset'),
                NavigationGroup::make('Laporan'),
                NavigationGroup::make('Pengaturan')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Beranda::class])
            ->widgets([])
            ->renderHook(PanelsRenderHook::TOPBAR_START, fn () => Blade::render('<x-ac.property-badge />'))
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => Blade::render('<x-ac.help-button />'))
            ->renderHook(PanelsRenderHook::FOOTER, fn () => Blade::render('<x-ac.footer />'))
            ->renderHook(PanelsRenderHook::BODY_END, fn () => Blade::render('<x-ac.login-ornament /><x-ac.bottom-nav />'))
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => new HtmlString('<meta name="theme-color" content="'.e(Branding::primary()).'"><style>'.Branding::cssVars().'</style>'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsurePasswordChanged::class,
            ]);
    }
}
