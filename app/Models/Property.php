<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Support\Branding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saved(fn () => Branding::flush());
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'room_count' => 'integer',
        ];
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
}
