<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Supprime la colonne orpheline charge_maximale_kw remplacée par charge_maximale (decimal).
     * Cette colonne était créée dans la migration initiale 2026_03_30_085420
     * mais n'est utilisée nulle part (cf. BUG #7).
     */
    public function up(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            if (Schema::hasColumn('departements', 'charge_maximale_kw')) {
                $table->dropColumn('charge_maximale_kw');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            if (! Schema::hasColumn('departements', 'charge_maximale_kw')) {
                $table->integer('charge_maximale_kw')->nullable()->comment('Colonne orpheline (remplacée par charge_maximale decimal)');
            }
        });
    }
};
