<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransferItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'received_scan' => 'boolean',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(AssetTransfer::class, 'transfer_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }
}
