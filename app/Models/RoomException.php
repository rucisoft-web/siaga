<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomException extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'room_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function substituteCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'substitute_category_id');
    }

    public const TYPES = ['quantity' => 'Jumlah berbeda', 'added' => 'Tambahan', 'not_available' => 'Tidak tersedia', 'substitute' => 'Substitusi'];
}
