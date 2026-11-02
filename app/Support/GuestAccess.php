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

    /**
     * Pastikan peran Tamu dan akun tamu ada untuk setiap perusahaan demo yang sudah terpasang
     * (mis. data demo dipasang dengan versi lama atau lewat impor SQL). Hanya properti data demo resmi (DemoSets).
     *
     * @return int jumlah akun tamu yang dibuat/diaktifkan
     */
    public static function repair(): int
    {
        if (! Role::where('slug', 'guest')->exists()) {
            PermissionCatalog::sync();
        }
        $n = 0;
        foreach (DemoSets::SETS as $class) {
            $p = Property::where('code', $class::CODE)->first();
            if ($p && \App\Models\Location::withoutGlobalScopes()->where('property_id', $p->id)->exists()) {
                static::ensure($p);
                $n++;
            }
        }

        return $n;
    }

    /** @return Collection<int, array{property: Property, user: User}> data demo yang bisa dijelajahi tamu */
    public static function datasets(): Collection
    {
        $role = Role::where('slug', 'guest')->value('id');
        $list = $role ? static::guestUsers($role) : collect();
        // mode tamu aktif tetapi akun tamu belum ada → perbaiki sekali, lalu baca ulang
        if ($list->isEmpty() && static::enabled() && ! static::$repaired) {
            static::$repaired = true;
            if (static::repair() > 0) {
                $role = Role::where('slug', 'guest')->value('id');
                $list = $role ? static::guestUsers($role) : collect();
            }
        }

        return $list;
    }

    protected static bool $repaired = false;

    protected static function guestUsers(int $role): Collection
    {
        return User::withoutGlobalScopes()->where('is_active', true)->whereHas('roles', fn ($q) => $q->whereKey($role))
            ->get()->map(fn (User $u) => ['property' => Property::find($u->property_id), 'user' => $u])
            ->filter(fn ($x) => $x['property'] && $x['property']->is_active)->sortBy(fn ($x) => $x['property']->id)->values();
    }
}
