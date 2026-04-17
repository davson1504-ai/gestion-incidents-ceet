<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_incidents', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->nullable()->unique();     // ex: DISJ, MT, VAND, SURC
            $table->string('libelle', 150);                       // ex: Disjonction franche, Manque de tension, Vandalisme
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index('libelle');
            $table->index('is_active');
            $table->index(['code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_incidents');
    }
};
