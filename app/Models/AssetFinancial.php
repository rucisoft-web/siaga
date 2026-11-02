<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class AssetFinancial extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'replacement_estimate' => 'decimal:2',
        ];
    }

    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
