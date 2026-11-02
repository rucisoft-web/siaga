<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->restrictOnDelete();
            $t->string('code', 30);
            $t->string('title');
            $t->string('type', 20)->default('preventive'); // preventive, inspection, calibration
            // sasaran: satu aset, ATAU kategori (opsional dibatasi lokasi & sub-lokasinya)
            $t->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('interval_days');
            $t->date('next_due');
            $t->unsignedSmallInteger('lead_days')->default(3)->comment('WO dibuat sekian hari sebelum jatuh tempo');
            $t->string('priority', 4)->default('P4');
            $t->decimal('estimated_hours', 6, 2)->nullable();
            $t->text('checklist')->nullable()->comment('satu langkah per baris');
            $t->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $t->string('regulation')->nullable()->comment('dasar kepatuhan, mis. Permenkes, K3');
            $t->boolean('is_active')->default(true);
            $t->foreignId('last_work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $t->timestamp('last_generated_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['property_id', 'code']);
            $t->index(['property_id', 'is_active', 'next_due']);
        });

        Schema::table('work_orders', function (Blueprint $t) {
            $t->foreignId('pm_schedule_id')->nullable()->after('type')->constrained()->nullOnDelete();
            $t->date('pm_due_date')->nullable()->after('pm_schedule_id');
            $t->unique(['pm_schedule_id', 'pm_due_date']);
        });

        Schema::table('vendors', function (Blueprint $t) {
            $t->string('contract_ref')->nullable()->after('sla_note');
            $t->date('contract_until')->nullable()->after('contract_ref');
        });

        \App\Support\PermissionCatalog::sync();
    }

    public function down(): void
    {
        Schema::table('vendors', fn (Blueprint $t) => $t->dropColumn(['contract_ref', 'contract_until']));
        Schema::table('work_orders', function (Blueprint $t) {
            $t->dropUnique(['pm_schedule_id', 'pm_due_date']);
            $t->dropConstrainedForeignId('pm_schedule_id');
            $t->dropColumn('pm_due_date');
        });
        Schema::dropIfExists('pm_schedules');
    }
};
