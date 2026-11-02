<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use Auditable, BelongsToProperty;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(RoomAssetTemplate::class);
    }
}
