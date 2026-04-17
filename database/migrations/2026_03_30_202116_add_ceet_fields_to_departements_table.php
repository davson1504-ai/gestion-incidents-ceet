<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            $table->string('direction_exploitation', 150)->nullable()->after('nom');
            $table->string('poste_repartition', 150)->nullable()->after('direction_exploitation');
            $table->string('transformateur', 150)->nullable()->after('poste_repartition');
            $table->string('arrivee', 100)->nullable()->after('transformateur');

            // Charge maximale avec unité séparée (plus flexible)
            $table->decimal('charge_maximale', 10, 2)->nullable()->after('arrivee');
            $table->string('charge_unite', 20)->default('A')->after('charge_maximale');

            // Index pour les recherches fréquentes
            $table->index('direction_exploitation');
            $table->index('poste_repartition');
            $table->index(['direction_exploitation', 'poste_repartition']);
        });
    }

    public function down(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            $table->dropIndex(['direction_exploitation']);
            $table->dropIndex(['poste_repartition']);
            $table->dropIndex(['direction_exploitation', 'poste_repartition']);

            $table->dropColumn([
                'direction_exploitation',
                'poste_repartition',
                'transformateur',
                'arrivee',
                'charge_maximale',
                'charge_unite',
            ]);
        });
    }
};
