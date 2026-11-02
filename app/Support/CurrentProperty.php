<?php

namespace App\Support;

use App\Models\Property;

/**
 * Properti aktif. Data awal satu hotel; skema memuat property_id agar ekspansi
 * tidak mengubah struktur. Pengguna dengan property_id hanya melihat properti tersebut.
 */
class CurrentProperty
{
    protected ?Property $property = null;

    protected bool $resolved = false;

    public function get(): ?Property
    {
        if (! $this->resolved) {
            $this->resolved = true;
            $id = auth()->user()?->property_id;
            $this->property = $id
                ? Property::find($id)
                : (Property::where('code', config('assetcare.property_code'))->first() ?? Property::orderBy('id')->first());
        }

        return $this->property;
    }

    public function id(): ?int
    {
        return $this->get()?->id;
    }

    public function set(?Property $property): void
    {
        $this->property = $property;
        $this->resolved = true;
    }
}
