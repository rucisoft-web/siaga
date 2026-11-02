<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Panduan penggunaan per layar. Isi ditulis sebagai Markdown di resources/help/{kunci}.md
 * (baris pertama "# Judul"), gambar di public/help/img/. Kunci = slug halaman/resource Filament.
 */
class Help
{
    /** urutan & pengelompokan di halaman daftar panduan */
    public const GROUPS = [
        'Mulai' => ['implementasi', 'beranda', 'dasbor-pimpinan', 'dasbor-grup', 'scan-qr', 'profil'],
        'Pendataan aset awal' => ['impor-master', 'pendataan', 'stok-label'],
        'Aset & label' => ['assets', 'struktur-lokasi', 'asset-quantity-groups', 'room-asset-templates', 'generate-assets', 'generation-batches', 'cetak-label', 'label-print-batches', 'konfirmasi-pemasangan', 'asset-transfers'],
        'Gangguan & pekerjaan' => ['service-requests', 'work-orders', 'room-blocks', 'laporan'],
        'Keuangan & audit' => ['penyusutan', 'stock-opname', 'penghapusan-aset'],
        'Data referensi' => ['locations', 'room-types', 'asset-categories', 'departments', 'vendors', 'sla-policies', 'approval-limits'],
        'Pengaturan & keamanan' => ['profil-hotel', 'users', 'roles', 'audit-logs'],
    ];

    public static function keyForRoute(?string $name): ?string
    {
        if (! $name) {
            return null;
        }
        if (preg_match('/^filament\.admin\.(?:pages|resources)\.([a-z0-9-]+)/', $name, $m)) {
            return $m[1];
        }
        if (str_starts_with($name, 'filament.admin.auth.profile')) {
            return 'profil';
        }

        return null;
    }

    public static function current(): ?string
    {
        $key = static::keyForRoute(request()->route()?->getName());

        return $key && $key !== 'implementasi' && static::has($key) ? $key : null;
    }

    public static function path(string $key): string
    {
        return resource_path('help/'.basename($key).'.md');
    }

    public static function has(string $key): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $key) && is_file(static::path($key));
    }

    /** @return array{title:string, html:string, summary:string} */
    public static function get(string $key): array
    {
        $file = static::path($key);
        $ver = filemtime($file);

        return Cache::remember('help:'.$key.':'.$ver.':'.Branding::code(), 3600, function () use ($file, $ver) {
            $md = (string) file_get_contents($file);
            $md = strtr($md, ['{APP}' => Branding::appName(), '{KODE}' => Branding::code(), '{HOTEL}' => Branding::hotelName()]);
            $title = trim(Str::after(strtok($md, "\n"), '# '));
            $body = Str::after($md, "\n");
            $summary = trim(strtok(trim($body), "\n"));
            // gambar relatif → /help/img/…
            $body = preg_replace_callback('/!\[([^\]]*)\]\(img\/([a-z0-9._-]+)\)/i', fn ($m) => '!['.$m[1].']('.asset('help/img/'.$m[2]).'?v='.$ver.')', $body);
            $html = Str::markdown($body, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
            $html = str_replace('<img ', '<img loading="lazy" ', $html);
            // jangkar untuk daftar isi
            $html = preg_replace_callback('/<h2>(.*?)<\/h2>/', fn ($m) => '<h2 id="'.Str::slug(html_entity_decode(strip_tags($m[1]))).'">'.$m[1].'</h2>', $html);

            return ['title' => $title, 'html' => $html, 'summary' => strip_tags(Str::markdown($summary))];
        });
    }

    /** @return array<string, array<string, array{title:string, summary:string}>> */
    public static function index(): array
    {
        $out = [];
        foreach (static::GROUPS as $group => $keys) {
            foreach ($keys as $k) {
                if (static::has($k)) {
                    $g = static::get($k);
                    $out[$group][$k] = ['title' => $g['title'], 'summary' => $g['summary']];
                }
            }
        }

        return $out;
    }
}
