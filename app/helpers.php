<?php

use App\Support\Terms;

if (! function_exists('istilah')) {
    /** Teks berisi istilah hotel → istilah jenis usaha perusahaan aktif (lihat App\Support\Terms). */
    function istilah(?string $text): string
    {
        return Terms::t($text);
    }
}
