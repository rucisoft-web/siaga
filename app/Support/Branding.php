<?php

namespace App\Support;

use App\Models\Property;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Throwable;

/**
 * Identitas hotel yang tampil di aplikasi (nama, logo, warna, zona waktu).
 * Sumber: Pengaturan › Profil hotel (tabel properties). Cadangan: config/assetcare.php (.env),
 * dipakai saat basis data belum siap (instalasi pertama, artisan sebelum migrate).
 */
class Branding
{
    protected static ?Property $property = null;

    protected static bool $loaded = false;

    /** properti milik pengguna yang sedang login (null = tamu/belum login) saat cache dibuat */
    protected static ?int $loadedFor = null;

    public static function property(): ?Property
    {
        $userProperty = null;
        try {
            $userProperty = auth()->hasUser() ? (app(CurrentProperty::class)->id() ?? auth()->user()?->property_id) : null;
        } catch (Throwable) {
        }
        if (static::$loaded && static::$loadedFor !== $userProperty) {
            static::$loaded = false;
        }
        if (! static::$loaded) {
            static::$loaded = true;
            static::$loadedFor = $userProperty;
            try {
                if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'primary_color')) {
                    static::$property = ($userProperty ? Property::find($userProperty) : null)
                        ?? Property::where('code', config('assetcare.property_code'))->first()
                        ?? Property::orderBy('id')->first();
                }
            } catch (Throwable) {
                static::$property = null;
            }
        }

        return static::$property;
    }

    public static function flush(): void
    {
        static::$loaded = false;
        static::$property = null;
    }

    public static function hotelName(): string
    {
        return static::property()?->name ?: (string) config('assetcare.hotel_name', 'Hotel Anda');
    }

    public static function appName(): string
    {
        return static::property()?->app_name ?: (string) config('assetcare.brand_name', 'SIAGA');
    }

    public static function tagline(): string
    {
        return static::property()?->tagline ?: (string) config('assetcare.brand_tagline', 'Sistem Informasi Aset & Gangguan');
    }

    public static function code(): string
    {
        return static::property()?->code ?: (string) config('assetcare.property_code', 'HTL');
    }

    public static function primary(): string
    {
        return static::validColor(static::property()?->primary_color, '#1F3A5F');
    }

    public static function accent(): string
    {
        return static::validColor(static::property()?->accent_color, '#C8102E');
    }

    public static function timezone(): string
    {
        $tz = static::property()?->timezone ?: config('assetcare.display_timezone', 'Asia/Jakarta');

        return in_array($tz, timezone_identifiers_list(), true) ? $tz : 'Asia/Jakarta';
    }

    /** Singkatan zona untuk label (WIB/WITA/WIT, selain itu UTC±). */
    public static function timezoneLabel(): string
    {
        return match (static::timezone()) {
            'Asia/Jakarta', 'Asia/Pontianak' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => now(static::timezone())->format('T'),
        };
    }

    public static function logoUrl(bool $dark = false): ?string
    {
        $p = static::property();
        $path = $dark ? ($p?->logo_dark_path ?: $p?->logo_path) : $p?->logo_path;
        if (! $path) {
            return null;
        }

        return route('brand.logo', ['variant' => $dark ? 'dark' : 'light', 'v' => substr(md5($path), 0, 8)]);
    }

    /** Logo untuk panel: gambar unggahan, atau logo teks bergaris bila belum ada. */
    public static function panelLogo(bool $dark = false): string|HtmlString
    {
        return static::logoUrl($dark) ?? static::textLogo();
    }

    public static function textLogo(): HtmlString
    {
        $name = e(static::hotelName());
        $app = e(static::appName());

        return new HtmlString(
            '<span class="ac-wordmark"><span class="ac-wordmark-mk" aria-hidden="true"><i></i><i></i><i></i></span>'
            .'<span class="ac-wordmark-tx"><b>'.$name.'</b><small>'.$app.'</small></span></span>'
        );
    }

    /** Variabel CSS warna untuk tema garis. */
    public static function cssVars(): string
    {
        return ':root{--p:'.static::primary().';--a:'.static::accent().';}';
    }

    protected static function validColor(?string $c, string $fallback): string
    {
        return $c && preg_match('/^#[0-9A-Fa-f]{6}$/', $c) ? strtoupper($c) : $fallback;
    }
}
