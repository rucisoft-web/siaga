<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetComponent extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'installed_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'component_asset_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
