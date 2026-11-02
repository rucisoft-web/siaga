<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Status pendataan aset awal per kamar/ruang. */
class RoomSurvey extends Model
{
    use Auditable, BelongsToProperty;

    protected $guarded = ['id'];

    public const STATUSES = ['in_progress' => 'Sedang didata', 'done' => 'Selesai'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function auditLabel(): string
    {
        return 'Pendataan '.($this->location?->labelText() ?? '#'.$this->location_id);
    }
}
