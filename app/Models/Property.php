<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use \App\Models\Concerns\Auditable;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saved(fn () => \App\Support\Branding::flush());
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'room_count' => 'integer',
        ];
    }

    public function departments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function locations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Location::class);
    }
}
