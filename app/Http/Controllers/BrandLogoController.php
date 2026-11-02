<?php

namespace App\Http\Controllers;

use App\Support\Branding;
use Illuminate\Support\Facades\Storage;

/** Menyajikan logo hotel dari penyimpanan privat (tanpa storage:link, aman di cPanel). */
class BrandLogoController extends Controller
{
    public function show(string $variant)
    {
        $p = Branding::property();
        $path = $variant === 'dark' ? ($p?->logo_dark_path ?: $p?->logo_path) : $p?->logo_path;
        abort_unless($path && str_starts_with($path, 'brand/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
