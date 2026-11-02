<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Jadwal pemeliharaan terencana (PM), inspeksi, atau kalibrasi yang membuat work order otomatis. */
class PmSchedule extends Model
{
    use Auditable, BelongsToProperty;

    protected $guarded = ['id'];

    public const TYPES = ['preventive' => 'Preventive maintenance', 'inspection' => 'Inspeksi', 'calibration' => 'Kalibrasi'];

    public const INTERVALS = [7 => 'Mingguan', 14 => '2 mingguan', 30 => 'Bulanan', 90 => 'Triwulan', 180 => 'Semester', 365 => 'Tahunan'];

    protected function casts(): array
    {
        return ['next_due' => 'date', 'last_generated_at' => 'datetime', 'is_active' => 'boolean', 'estimated_hours' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $s) {
            $s->code ??= \App\Support\Sequence::next('PM', $s->property_id ?? app(\App\Support\CurrentProperty::class)->id());
            $s->created_by ??= auth()->id();
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lastWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'last_work_order_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function intervalLabel(): string
    {
        return self::INTERVALS[$this->interval_days] ?? 'Tiap '.$this->interval_days.' hari';
    }

    public function targetLabel(): string
    {
        if ($this->asset) {
            return $this->asset->asset_code.' '.$this->asset->name;
        }

        return trim(($this->category?->name ?? 'Semua kategori').($this->location ? ' · '.($this->location->full_path ?: $this->location->name) : ''));
    }
}
