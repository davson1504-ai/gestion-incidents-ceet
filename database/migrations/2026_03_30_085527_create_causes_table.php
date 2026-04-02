<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('causes', function (Blueprint $table) {
            $table->id();
            
            $table->string('code', 50)->nullable()->unique();           // ex: SURC, VAND, DEL, INTEMP
            $table->string('libelle', 150);                             // ex: Surcharge, Vandalisme, Délestage, Intempéries
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Relation avec le type d'incident
            $table->foreignId('type_incident_id')
                  ->nullable()
                  ->constrained('type_incidents')
                  ->onDelete('set null');
            
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index('libelle');
            $table->index('is_active');
            $table->index(['type_incident_id', 'is_active']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('causes');
    }
};