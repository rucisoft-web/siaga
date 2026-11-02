<?php

namespace App\Support;

use Database\Seeders\GpchDemoSeeder;
use Database\Seeders\HerminaDemoSeeder;
use Database\Seeders\KimiaFarmaDemoSeeder;

/** Daftar data demo (masing-masing perusahaan terpisah) untuk situs pra-workshop. */
class DemoSets
{
    public const SETS = [
        'gpch' => GpchDemoSeeder::class,
        'hermina' => HerminaDemoSeeder::class,
        'kimia-farma' => KimiaFarmaDemoSeeder::class,
    ];

    /** @return array<string, array{key:string, code:string, class:class-string, title:string, sector:string, summary:string, source:string}> */
    public static function all(): array
    {
        $out = [];
        foreach (self::SETS as $key => $class) {
            $out[$key] = ['key' => $key, 'code' => $class::CODE, 'class' => $class] + $class::about();
        }

        return $out;
    }

    public static function byCode(string $code): ?array
    {
        return collect(self::all())->first(fn ($s) => $s['code'] === $code);
    }
}
