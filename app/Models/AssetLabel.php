<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToProperty;
use App\Support\Sequence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

/**
 * Token label QR: 128 bit acak dari CSPRNG, dipetakan ke tepat satu aset atau kelompok.
 * Pencarian memakai hash SHA-256; token utuh disimpan terenkripsi hanya untuk cetak ulang.
 */
class AssetLabel extends Model
{
    use Auditable, BelongsToProperty;

    protected $guarded = ['id'];

    protected $hidden = ['token_ciphertext', 'token_hash'];

    public const STATUSES = ['active' => 'Aktif', 'revoked' => 'Tidak berlaku', 'unassigned' => 'Stok kosong (belum terhubung)'];

    protected function casts(): array
    {
        return ['activated_at' => 'datetime', 'revoked_at' => 'datetime', 'bound_at' => 'datetime'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function quantityGroup(): BelongsTo
    {
        return $this->belongsTo(AssetQuantityGroup::class, 'quantity_group_id');
    }

    public function printItems(): HasMany
    {
        return $this->hasMany(LabelPrintItem::class, 'asset_label_id');
    }

    public function installationEvents(): HasMany
    {
        return $this->hasMany(InstallationEvent::class, 'asset_label_id')->orderByDesc('occurred_at')->orderByDesc('id');
    }

    public function latestInstallation(): HasOne
    {
        return $this->hasOne(InstallationEvent::class, 'asset_label_id')->latestOfMany('occurred_at');
    }

    public static function newToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function findByToken(string $token): ?self
    {
        if (! preg_match('/^[A-Za-z0-9_-]{20,64}$/', $token)) {
            return null;
        }

        return static::withoutGlobalScopes()->where('token_hash', self::hashToken($token))->first();
    }

    public function token(): string
    {
        return Crypt::decryptString($this->token_ciphertext);
    }

    public function url(): string
    {
        return config('assetcare.qr_base_url').'/q/'.$this->token();
    }

    public function targetKey(): string
    {
        return $this->asset_id ? 'a:'.$this->asset_id : 'g:'.$this->quantity_group_id;
    }

    /** Terbitkan label aktif untuk aset/kelompok (satu token aktif per target). */
    public static function issueFor(?Asset $asset = null, ?AssetQuantityGroup $group = null): self
    {
        abort_unless(($asset === null) !== ($group === null), 500, 'Label harus tepat satu target');
        $token = self::newToken();
        $target = $asset ?? $group;

        return static::create([
            'property_id' => $target->property_id,
            'asset_id' => $asset?->id,
            'quantity_group_id' => $group?->id,
            'token_hash' => self::hashToken($token),
            'token_ciphertext' => Crypt::encryptString($token),
            'status' => 'active',
            'activated_at' => now(),
            'active_target_key' => $asset ? 'a:'.$asset->id : 'g:'.$group->id,
        ]);
    }

    /** Label stok kosong: dicetak lebih dulu, dihubungkan ke aset saat ditempel di lapangan. */
    public function isBlank(): bool
    {
        return $this->status === 'unassigned';
    }

    public static function issueBlank(int $propertyId): self
    {
        $token = self::newToken();
        $code = Property::whereKey($propertyId)->value('code') ?? 'X';

        return static::create([
            'property_id' => $propertyId,
            'stock_code' => sprintf('%s-QR-%06d', $code, Sequence::increment($propertyId, 'QRS')),
            'token_hash' => self::hashToken($token),
            'token_ciphertext' => Crypt::encryptString($token),
            'status' => 'unassigned',
        ]);
    }

    public function displayCode(): string
    {
        return $this->asset?->asset_code ?? $this->quantityGroup?->group_code ?? $this->stock_code ?? '#'.$this->id;
    }

    public function currentInstallationStatus(): string
    {
        return $this->latestInstallation?->status ?? 'pending';
    }
}
