<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Konsolidasi grup tidak dipakai: setiap sesi hanya melihat satu perusahaan. */
return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'group-director')->value('id');
        if ($role) {
            DB::table('role_user')->where('role_id', $role)->delete();
            DB::table('permission_role')->where('role_id', $role)->delete();
            DB::table('roles')->where('id', $role)->delete();
        }
        $perm = DB::table('permissions')->where('slug', 'group.view')->value('id');
        if ($perm) {
            DB::table('permission_role')->where('permission_id', $perm)->delete();
            DB::table('permissions')->where('id', $perm)->delete();
        }
        DB::table('users')->where('email', 'direksi@grup.demo')->update(['is_active' => false]);
    }

    public function down(): void {}
};
