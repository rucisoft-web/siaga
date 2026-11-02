<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotAssignment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(RoomAssetSlot::class, 'slot_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function quantityGroup(): BelongsTo
    {
        return $this->belongsTo(AssetQuantityGroup::class, 'quantity_group_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
