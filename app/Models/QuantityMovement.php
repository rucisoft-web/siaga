<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantityMovement extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'qty_before' => 'decimal:2',
            'qty_after' => 'decimal:2',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AssetQuantityGroup::class, 'quantity_group_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
