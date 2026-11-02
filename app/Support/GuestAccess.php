<?php

namespace App\Support;

use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Mode tamu untuk situs demo / pra-workshop: pengunjung masuk tanpa sandi ke akun bersama berperan "Tamu (demo)"
 * yang hanya bisa melihat. Aktif hanya bila SIAGA_GUEST_MODE=true — JANGAN diaktifkan pada instalasi berisi data asli.
 */
class GuestAccess
{
    public static function enabled(): bool
    {
        return (bool) config('assetcare.guest_mode');
    }

    public static function email(Property $p): string
    {
        return 'tamu.'.Str::lower($p->code).'@'.config('assetcare.demo_email_domain', 'hotel.test');
    }

    /** Buat/aktifkan akun tamu untuk properti (dipanggil perintah data demo). */
    public static function ensure(Property $p): User
    {
        $u = User::withoutGlobalScopes()->firstOrNew(['email' => static::email($p)]);
        $u->forceFill([
            'name' => 'Tamu demo', 'job_title' => 'Pengunjung (hanya melihat)', 'property_id' => $p->id, 'is_active' => true,
            'must_change_password' => false, 'password' => $u->password ?: Str::password(32),
        ])->save();
        $role = Role::where('slug', 'guest')->value('id');
        $u->roles()->syncWithoutDetaching([$role => ['property_id' => $p->id]]);

        return $u;
    }

    /** @return Collection<int, array{property: Property, user: User}> data demo yang bisa dijelajahi tamu */
    public static function datasets(): Collection
    {
        $role = Role::where('slug', 'guest')->value('id');
        if (! $role) {
            return collect();
        }

        return User::withoutGlobalScopes()->where('is_active', true)->whereHas('roles', fn ($q) => $q->whereKey($role))
            ->get()->map(fn (User $u) => ['property' => Property::find($u->property_id), 'user' => $u])
            ->filter(fn ($x) => $x['property'] && $x['property']->is_active)->sortBy(fn ($x) => $x['property']->id)->values();
    }
}
