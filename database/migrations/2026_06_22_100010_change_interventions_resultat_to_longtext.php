<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('interventions') || ! Schema::hasColumn('interventions', 'resultat')) {
            return;
        }

        // MySQL/MariaDB : évite l'erreur SQLSTATE[22001] quand le rapport de résolution est long.
        Schema::table('interventions', function (Blueprint $table): void {
            $table->longText('resultat')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('interventions') || ! Schema::hasColumn('interventions', 'resultat')) {
            return;
        }

        // Retour conservateur. Attention : les valeurs longues seraient tronquées si rollback manuel.
        Schema::table('interventions', function (Blueprint $table): void {
            $table->string('resultat')->nullable()->change();
        });
    }
};
