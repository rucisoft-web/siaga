<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenerationResult extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'detail' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(GenerationBatch::class, 'batch_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'room_id');
    }
}
