<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTakeItem extends Model
{
    protected $guarded = ['id'];

    public const RESULTS = [
        'pending' => 'Belum dihitung', 'found' => 'Ditemukan', 'moved' => 'Ditemukan di lokasi lain',
        'not_found' => 'Tidak ditemukan', 'unregistered' => 'Tidak terdaftar (temuan baru)',
    ];

    protected function casts(): array
    {
        return ['counted_at' => 'datetime'];
    }

    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class)->withoutGlobalScopes();
    }

    public function expectedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'expected_location_id')->withoutGlobalScopes();
    }

    public function foundLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'found_location_id')->withoutGlobalScopes();
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }
}
