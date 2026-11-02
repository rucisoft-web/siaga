<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Penyusutan aset tetap (PSAK 16): kebijakan per kategori, dapat ditimpa per aset. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $t) {
            $t->string('depreciation_method', 20)->default('straight_line')->after('default_pm_interval_days');
            $t->unsignedSmallInteger('useful_life_months')->nullable()->after('depreciation_method');
            $t->decimal('residual_pct', 5, 2)->default(0)->after('useful_life_months');
            $t->string('gl_asset_account', 30)->nullable()->after('residual_pct');
            $t->string('gl_expense_account', 30)->nullable()->after('gl_asset_account');
            $t->string('gl_accum_account', 30)->nullable()->after('gl_expense_account');
        });
        Schema::table('asset_financials', function (Blueprint $t) {
            $t->date('depreciation_start')->nullable()->after('acquisition_cost');
            $t->string('depreciation_method', 20)->nullable()->after('depreciation_start');
            $t->unsignedSmallInteger('useful_life_months')->nullable()->after('depreciation_method');
            $t->decimal('residual_value', 15, 2)->nullable()->after('useful_life_months');
        });

        // masa manfaat awal per kelompok (dapat diubah di Data referensi › Kategori aset)
        $life = ['it' => 48, 'kamar' => 96, 'mice' => 96, 'kitchen' => 96, 'mep' => 120, 'publik' => 96, 'keselamatan' => 96];
        foreach ($life as $g => $m) {
            DB::table('asset_categories')->where('group', $g)->whereNull('useful_life_months')->update(['useful_life_months' => $m]);
        }
        DB::table('asset_categories')->whereNull('useful_life_months')->update(['useful_life_months' => 96]);
    }

    public function down(): void
    {
        Schema::table('asset_financials', fn (Blueprint $t) => $t->dropColumn(['depreciation_start', 'depreciation_method', 'useful_life_months', 'residual_value']));
        Schema::table('asset_categories', fn (Blueprint $t) => $t->dropColumn(['depreciation_method', 'useful_life_months', 'residual_pct', 'gl_asset_account', 'gl_expense_account', 'gl_accum_account']));
    }
};
