<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Profil hotel: identitas & tampilan diatur dari aplikasi (bukan kode), agar satu produk bisa dipakai hotel mana saja. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $t) {
            $t->string('legal_name')->nullable()->after('name');
            $t->string('city', 80)->nullable()->after('address');
            $t->string('phone', 40)->nullable()->after('city');
            $t->string('email')->nullable()->after('phone');
            $t->string('star_rating', 10)->nullable()->after('email');
            $t->unsignedSmallInteger('room_count')->nullable()->after('star_rating');
            $t->string('app_name', 60)->nullable()->after('room_count');
            $t->string('tagline', 120)->nullable()->after('app_name');
            $t->string('logo_path')->nullable()->after('tagline');
            $t->string('logo_dark_path')->nullable()->after('logo_path');
            $t->string('primary_color', 7)->default('#1F3A5F')->after('logo_dark_path');
            $t->string('accent_color', 7)->default('#C8102E')->after('primary_color');
            $t->string('currency', 3)->default('IDR')->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $t) {
            $t->dropColumn(['legal_name', 'city', 'phone', 'email', 'star_rating', 'room_count', 'app_name', 'tagline',
                'logo_path', 'logo_dark_path', 'primary_color', 'accent_color', 'currency']);
        });
    }
};
