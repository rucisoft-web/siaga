<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'device_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
