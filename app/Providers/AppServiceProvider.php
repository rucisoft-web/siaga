<?php

namespace App\Providers;

use App\Models\User;
use App\Support\CurrentProperty;
use App\Support\PermissionCatalog;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentProperty::class);
    }

    public function boot(): void
    {
        // Timestamp tersimpan UTC; tampilan & input memakai zona waktu hotel (Pengaturan › Profil hotel)
        \Filament\Facades\Filament::serving(fn () => FilamentTimezone::set(\App\Support\Fmt::tz()));
        \Filament\Forms\Components\DatePicker::configureUsing(fn ($c) => $c->native(false)->displayFormat('d/m/Y'));
        \Filament\Forms\Components\DateTimePicker::configureUsing(fn ($c) => $c->native(false)->displayFormat('d/m/Y H:i')->seconds(false));
        \Filament\Tables\Table::configureUsing(fn ($t) => $t->defaultPaginationPageOption(25)->striped());

        FilamentAsset::register([
            Css::make('siaga', asset('css/assetcare.css').'?v='.@filemtime(public_path('css/assetcare.css'))),
            Js::make('jsqr', asset('js/vendor/jsQR.js').'?v=1.4.0'),
            Js::make('asset-care-scan', asset('js/assetcare-scan.js').'?v=1'),
        ]);

        Password::defaults(fn () => Password::min(10)->letters()->numbers());

        // Izin dapat dipakai lewat Gate/@can: Gate::allows('wo.verify')
        Gate::before(function (User $user, string $ability) {
            if (array_key_exists($ability, PermissionCatalog::PERMISSIONS)) {
                return $user->hasPermission($ability);
            }

            return null;
        });

        RateLimiter::for('login', fn ($request) => Limit::perMinute(5)->by(($request->input('email') ?? '').'|'.$request->ip()));

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });

        if (! $this->app->runningInConsole()) {
            request()->attributes->set('request_id', (string) Str::ulid());
        }
    }
}
