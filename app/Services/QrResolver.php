<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLabel;
use App\Models\AssetQuantityGroup;

/** Validasi host & format payload QR. QR asing tidak dibuka otomatis. */
class QrResolver
{
    public function allowedHosts(): array
    {
        $hosts = [parse_url(config('assetcare.qr_base_url'), PHP_URL_HOST), parse_url(config('app.url'), PHP_URL_HOST)];

        return array_values(array_unique(array_filter(array_merge($hosts, config('assetcare.qr_extra_hosts', [])))));
    }

    /**
     * @return array{type:string, label?:AssetLabel, asset?:Asset, group?:AssetQuantityGroup, message?:string}
     *   type: label | asset | group | foreign | invalid | revoked | unknown
     */
    public function resolve(string $input): array
    {
        $input = trim($input);
        if ($input === '') {
            return ['type' => 'invalid', 'message' => 'Isian kosong.'];
        }
        if (preg_match('#^https?://#i', $input)) {
            $host = parse_url($input, PHP_URL_HOST);
            $path = (string) parse_url($input, PHP_URL_PATH);
            if (! in_array($host, $this->allowedHosts(), true)) {
                return ['type' => 'foreign', 'message' => 'QR bukan label '.\App\Support\Branding::appName().' ('.$host.'). Tidak dibuka.'];
            }
            if (! preg_match('#/q/([A-Za-z0-9_-]{20,64})$#', $path, $m)) {
                return ['type' => 'invalid', 'message' => 'Format QR tidak dikenal.'];
            }

            return $this->fromToken($m[1]);
        }
        if (preg_match('/^[A-Z0-9]{2,10}-(AST|QTY|QR)-\d{4,8}$/i', $input)) {
            $code = strtoupper($input);
            if (str_contains($code, '-QR-')) {
                $l = AssetLabel::where('stock_code', $code)->first();

                return $l ? $this->fromLabel($l) : ['type' => 'unknown', 'message' => 'Nomor label stok tidak ditemukan.'];
            }
            if (str_contains($code, '-QTY-')) {
                $g = AssetQuantityGroup::where('group_code', $code)->first();

                return $g ? ['type' => 'group', 'group' => $g, 'label' => $g->activeLabel] : ['type' => 'unknown', 'message' => 'Kode kelompok tidak ditemukan.'];
            }
            $a = Asset::where('asset_code', $code)->first();

            return $a ? ['type' => 'asset', 'asset' => $a, 'label' => $a->activeLabel] : ['type' => 'unknown', 'message' => 'Kode aset tidak ditemukan.'];
        }
        if (preg_match('/^[A-Za-z0-9_-]{20,64}$/', $input)) {
            return $this->fromToken($input);
        }

        return ['type' => 'invalid', 'message' => 'Masukkan URL label, atau kode aset seperti '.\App\Support\Branding::code().'-AST-000001.'];
    }

    public function fromToken(string $token): array
    {
        $label = AssetLabel::findByToken($token);
        if (! $label) {
            return ['type' => 'unknown', 'message' => 'Label tidak dikenal.'];
        }

        return $this->fromLabel($label);
    }

    public function fromLabel(AssetLabel $label): array
    {
        if ($label->isBlank()) {
            return ['type' => 'blank', 'label' => $label, 'message' => 'Label stok '.$label->stock_code.' belum terhubung ke aset. Hubungkan lewat menu Pendataan aset.'];
        }
        if ($label->status !== 'active') {
            return ['type' => 'revoked', 'label' => $label, 'message' => 'Label ini sudah tidak berlaku.'];
        }

        return $label->asset_id
            ? ['type' => 'asset', 'asset' => $label->asset, 'label' => $label]
            : ['type' => 'group', 'group' => $label->quantityGroup, 'label' => $label];
    }
}
