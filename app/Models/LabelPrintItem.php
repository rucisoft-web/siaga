<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelPrintItem extends Model
{
    protected $guarded = ['id'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LabelPrintBatch::class, 'batch_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(AssetLabel::class, 'asset_label_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function quantityGroup(): BelongsTo
    {
        return $this->belongsTo(AssetQuantityGroup::class, 'quantity_group_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
