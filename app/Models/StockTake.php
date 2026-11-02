<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToProperty;
use App\Support\Sequence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Sesi stock opname (inventarisasi fisik berkala) untuk satu cakupan lokasi. */
class StockTake extends Model
{
    use Auditable, BelongsToProperty;

    protected $guarded = ['id', 'number'];

    public const STATUSES = ['counting' => 'Sedang dihitung', 'closed' => 'Ditutup', 'cancelled' => 'Dibatalkan'];

    protected function casts(): array
    {
        return ['cutoff_date' => 'date', 'closed_at' => 'datetime', 'applied' => 'boolean', 'team' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $t) => $t->number ??= Sequence::next('SO', $t->property_id));
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTakeItem::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
