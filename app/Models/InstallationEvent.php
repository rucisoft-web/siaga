<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
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

    public function printItem(): BelongsTo
    {
        return $this->belongsTo(LabelPrintItem::class, 'print_item_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public const STATUSES = ['pending' => 'Belum terpasang', 'installed' => 'Terpasang', 'damaged' => 'Label rusak', 'missing' => 'Label hilang', 'needs_relabel' => 'Perlu label ulang'];
}
