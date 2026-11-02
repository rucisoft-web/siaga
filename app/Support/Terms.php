<?php

namespace App\Support;

/**
 * Istilah per jenis usaha. Teks aplikasi ditulis dengan istilah hotel; istilah() menggantinya sesuai
 * jenis usaha perusahaan aktif (rumah sakit, ritel/outlet, umum). Satu sesi = satu perusahaan, jadi tidak tercampur.
 */
class Terms
{
    public const TYPES = [
        'hotel' => 'Hotel / resort',
        'hospital' => 'Rumah sakit / klinik',
        'retail' => 'Ritel / jaringan outlet (apotek, toko, cabang)',
        'general' => 'Umum (kantor, pabrik, kampus, gedung)',
    ];

    /** frasa hotel (huruf kecil) → frasa jenis usaha lain; frasa panjang didahulukan otomatis oleh strtr */
    protected const MAP = [
        'hospital' => [
            'tipe kamar' => 'kelas perawatan', 'jenis kamar' => 'kelas perawatan', 'blokir kamar' => 'tutup kamar', 'kamar diblokir' => 'kamar ditutup',
            'hari-kamar' => 'hari-kamar', 'front office' => 'admisi', 'tidak dijual' => 'tidak dipakai', 'siap dijual' => 'siap dipakai',
            'dijual kembali' => 'dipakai kembali', 'hotel' => 'rumah sakit', 'housekeeping' => 'pelayanan kamar', 'pms' => 'SIMRS',
        ],
        'retail' => [
            'kamar mandi' => 'kamar mandi', 'kamar/ruang' => 'outlet/ruang', 'kamar & ruang' => 'outlet & ruang', 'tipe kamar' => 'format outlet', 'jenis kamar' => 'format outlet',
            'nomor kamar' => 'kode outlet', 'blokir kamar' => 'tutup outlet', 'kamar diblokir' => 'outlet ditutup', 'hari-kamar' => 'hari-outlet',
            'kamar' => 'outlet', 'lantai & area' => 'rayon & area', 'lantai' => 'rayon', 'front office' => 'kepala outlet',
            'tidak dijual' => 'tidak beroperasi', 'siap dijual' => 'siap beroperasi', 'dijual kembali' => 'beroperasi kembali', 'hotel' => 'perusahaan',
            'housekeeping' => 'staf outlet', 'pms' => 'sistem POS', 'koridor' => 'area outlet', 'zona' => 'wilayah',
        ],
        'general' => [
            'kamar mandi' => 'kamar mandi', 'kamar/ruang' => 'ruang', 'kamar & ruang' => 'ruang', 'tipe kamar' => 'jenis ruang', 'jenis kamar' => 'jenis ruang',
            'nomor kamar' => 'nomor ruang', 'blokir kamar' => 'tutup ruang', 'kamar diblokir' => 'ruang ditutup', 'hari-kamar' => 'hari-ruang',
            'kamar' => 'ruang', 'front office' => 'pengelola gedung', 'tidak dijual' => 'tidak dipakai', 'siap dijual' => 'siap dipakai',
            'dijual kembali' => 'dipakai kembali', 'hotel' => 'perusahaan', 'housekeeping' => 'kebersihan', 'pms' => 'sistem ruang',
        ],
    ];

    protected static array $cache = [];

    public static function type(): string
    {
        $t = Branding::property()?->business_type;

        return isset(self::TYPES[$t]) ? $t : 'hotel';
    }

    public static function t(?string $text, ?string $type = null): string
    {
        $type ??= self::type();
        if ($text === null || $text === '' || $type === 'hotel') {
            return (string) $text;
        }

        return strtr($text, self::$cache[$type] ??= self::pairs($type));
    }

    /** satu kata/frasa, mis. Terms::word('kamar') → "outlet" */
    public static function word(string $hotelWord, bool $ucfirst = false): string
    {
        $w = self::t($hotelWord);

        return $ucfirst ? mb_strtoupper(mb_substr($w, 0, 1)).mb_substr($w, 1) : $w;
    }

    protected static function pairs(string $type): array
    {
        $out = [];
        foreach (self::MAP[$type] ?? [] as $from => $to) {
            $out[$from] = $to;
            $out[mb_strtoupper(mb_substr($from, 0, 1)).mb_substr($from, 1)] = mb_strtoupper(mb_substr($to, 0, 1)).mb_substr($to, 1);
            $out[mb_strtoupper($from)] = mb_strtoupper($to);
        }

        return $out;
    }
}
