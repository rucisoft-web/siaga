<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Jenis usaha menentukan istilah di layar & label (kamar / outlet / ruang). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $t) {
            $t->string('business_type', 20)->default('hotel')->after('legal_name');
        });
        DB::table('properties')->where('code', 'HRM')->update(['business_type' => 'hospital']);
        DB::table('properties')->where('code', 'KF')->update(['business_type' => 'retail']);
    }

    public function down(): void
    {
        Schema::table('properties', fn (Blueprint $t) => $t->dropColumn('business_type'));
    }
};
