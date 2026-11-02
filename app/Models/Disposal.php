<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToProperty;
use App\Support\Sequence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Usulan & pelaksanaan penghapusan aset tetap (jual, musnah, hibah, tukar tambah, hilang). */
class Disposal extends Model
{
    use Auditable, BelongsToProperty;

    protected $guarded = ['id', 'number'];

    public const METHODS = ['sale' => 'Dijual', 'scrap' => 'Dimusnahkan / scrap', 'donation' => 'Dihibahkan', 'trade_in' => 'Tukar tambah', 'lost' => 'Hilang (hasil stock opname)'];

    public const STATUSES = ['submitted' => 'Diusulkan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai (aset dihapus)', 'cancelled' => 'Dibatalkan'];

    protected function casts(): array
    {
        return ['proposed_date' => 'date', 'completed_date' => 'date', 'approved_at' => 'datetime',
            'book_value' => 'decimal:2', 'acquisition_cost' => 'decimal:2', 'accumulated' => 'decimal:2', 'proceeds' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $d) => $d->number ??= Sequence::next('HPS', $d->property_id));
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    /** Laba (rugi) pelepasan = hasil penjualan − nilai buku. */
    public function gainLoss(): float
    {
        return (float) ($this->proceeds ?? 0) - (float) $this->book_value;
    }
}
