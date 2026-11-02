<?php

use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Stock opname / inventarisasi fisik berkala dan penghapusan aset (disposal). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_takes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('number', 30);
            $t->string('title');
            $t->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->date('cutoff_date');
            $t->string('status', 20)->default('counting'); // counting, closed, cancelled
            $t->text('notes')->nullable();
            $t->json('team')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('closed_at')->nullable();
            $t->boolean('applied')->default(false);
            $t->timestamps();
            $t->unique(['property_id', 'number']);
        });
        Schema::create('stock_take_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stock_take_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $t->foreignId('expected_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->foreignId('found_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->string('result', 20)->default('pending'); // pending, found, moved, not_found, unregistered
            $t->char('expected_grade', 1)->nullable();
            $t->char('found_grade', 1)->nullable();
            $t->string('description')->nullable();  // untuk aset tak terdaftar
            $t->string('note')->nullable();
            $t->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('counted_at')->nullable();
            $t->timestamps();
            $t->index(['stock_take_id', 'result']);
            $t->index(['stock_take_id', 'expected_location_id']);
        });

        Schema::create('disposals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('number', 30);
            $t->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $t->string('method', 20);       // sale, scrap, donation, trade_in, lost
            $t->string('reason');
            $t->text('justification')->nullable();
            $t->date('proposed_date');
            $t->decimal('book_value', 15, 2)->default(0);
            $t->decimal('acquisition_cost', 15, 2)->default(0);
            $t->decimal('accumulated', 15, 2)->default(0);
            $t->decimal('proceeds', 15, 2)->nullable();
            $t->string('buyer')->nullable();
            $t->string('status', 20)->default('submitted'); // submitted, approved, rejected, completed, cancelled
            $t->foreignId('stock_take_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->string('decision_note')->nullable();
            $t->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->date('completed_date')->nullable();
            $t->string('document_ref')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'number']);
        });

        PermissionCatalog::sync();
    }

    public function down(): void
    {
        Schema::dropIfExists('disposals');
        Schema::dropIfExists('stock_take_items');
        Schema::dropIfExists('stock_takes');
    }
};
