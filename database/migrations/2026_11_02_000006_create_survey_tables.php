<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pendataan aset awal di lapangan:
 * - stok label QR kosong (dicetak dulu, dihubungkan ke aset saat ditempel)
 * - status pendataan per kamar/ruang (siapa, kapan, hasil)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_labels', function (Blueprint $t) {
            $t->string('stock_code', 30)->nullable()->unique()->after('quantity_group_id')->comment('nomor label stok kosong, mis. HTL-QR-000001');
            $t->foreignId('bound_by')->nullable()->after('revoked_by')->constrained('users')->nullOnDelete();
            $t->timestamp('bound_at')->nullable()->after('bound_by');
        });
        // label stok belum punya waktu aktif
        Schema::table('asset_labels', function (Blueprint $t) {
            $t->timestamp('activated_at')->nullable()->change();
        });

        Schema::create('room_surveys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->restrictOnDelete();
            $t->foreignId('location_id')->unique()->constrained()->restrictOnDelete();
            $t->string('status', 12)->default('in_progress'); // in_progress, done
            $t->foreignId('surveyor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->unsignedSmallInteger('found_count')->default(0);
            $t->unsignedSmallInteger('missing_count')->default(0);
            $t->unsignedSmallInteger('extra_count')->default(0);
            $t->string('note')->nullable();
            $t->timestamps();
            $t->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_surveys');
        Schema::table('asset_labels', function (Blueprint $t) {
            $t->dropConstrainedForeignId('bound_by');
            $t->dropColumn(['stock_code', 'bound_at']);
        });
    }
};
