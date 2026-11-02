<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBlock extends Model
{
    use Auditable;

    protected static function booted(): void
    {
        // Tidak punya property_id sendiri: batasi lewat kamar/ruang agar tidak bocor antarperusahaan.
        static::addGlobalScope('property', fn ($q) => $q->whereHas('room'));
    }

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'fo_confirmed_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'room_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fo_confirmed_by');
    }

    public const STATUSES = ['proposed' => 'Diusulkan', 'confirmed' => 'Dikonfirmasi FO', 'rejected' => 'Ditolak FO', 'released' => 'Dilepas'];
}
