<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetFinancial extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'depreciation_start' => 'date',
            'residual_value' => 'decimal:2',
            'acquisition_cost' => 'decimal:2',
            'replacement_estimate' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
