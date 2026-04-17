<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incident_id')
                ->constrained('incidents')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');           // L'utilisateur qui a effectué l'action

            // Type d'action (pour filtrage et historique clair)
            $table->string('action_type', 100);     // ex: creation, update, assignation, resolution, cloture, commentaire

            $table->text('description');            // Description lisible de l'action

            $table->dateTime('action_date');        // Date et heure exacte de l'action

            // Pour garder l'historique détaillé (optionnel mais très utile pour l'audit)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->timestamps();

            // Index importants pour les performances
            $table->index('incident_id');
            $table->index('user_id');
            $table->index('action_type');
            $table->index('action_date');
            $table->index(['incident_id', 'action_date']);   // Pour voir l'historique chronologique d'un incident
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_actions');
    }
};
