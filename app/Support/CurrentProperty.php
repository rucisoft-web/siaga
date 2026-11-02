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
            $user = auth()->user();
            $id = $user?->property_id;
            // direksi grup / super admin dapat berpindah perusahaan aktif (disimpan di sesi)
            if ($user && ($switch = static::sessionProperty()) && static::canSwitch($user)) {
                $id = $switch;
            }
            $this->property = $id
                ? Property::find($id)
                : (Property::where('code', config('assetcare.property_code'))->first() ?? Property::orderBy('id')->first());
        }

        return $this->property;
    }

    /** Pengguna dengan peran global (tanpa ikatan perusahaan) yang memiliki izin group.view, atau Super Admin. */
    public static function canSwitch(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }
        $user->loadMissing('roles.permissions');

        return $user->roles->contains(fn ($r) => $r->pivot->property_id === null
            && ($r->slug === 'super-admin' || $r->permissions->contains('slug', 'group.view')));
    }

    protected static function sessionProperty(): ?int
    {
        try {
            $v = request()->hasSession() ? request()->session()->get('active_property_id') : null;
        } catch (\Throwable) {
            $v = null;
        }

        return $v && Property::whereKey($v)->where('is_active', true)->exists() ? (int) $v : null;
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
