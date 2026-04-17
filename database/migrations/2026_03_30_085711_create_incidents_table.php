<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('code_incident', 50)->unique();           // ex: INC-20260330-00145
            $table->string('titre')->nullable();                     // Optionnel, mais utile
            $table->text('description')->nullable();

            // Relations principales (catalogues)
            $table->foreignId('departement_id')
                ->constrained('departements')
                ->onDelete('cascade');

            $table->foreignId('type_incident_id')
                ->constrained('type_incidents')
                ->onDelete('cascade');

            $table->foreignId('cause_id')
                ->nullable()
                ->constrained('causes')
                ->onDelete('set null');

            // Statut & Priorité
            $table->foreignId('status_id')
                ->constrained('statuses')
                ->onDelete('cascade');

            $table->foreignId('priorite_id')
                ->constrained('priorites')
                ->onDelete('cascade');

            // Localisation et détails terrain
            $table->text('localisation')->nullable();                // Zone, ligne, poste, quartier...

            // Dates et durée
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->integer('duree_minutes')->nullable();            // Calcul automatique à la clôture

            // Responsables
            $table->foreignId('operateur_id')                        // Celui qui déclare l'incident
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('responsable_id')                      // Opérateur terrain en charge
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('superviseur_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // Résolution
            $table->text('actions_menees')->nullable();
            $table->text('resolution_summary')->nullable();
            $table->dateTime('clotured_at')->nullable();

            $table->timestamps();

            // Index pour performances (recherches fréquentes sur le tableau de bord)
            $table->index('code_incident');
            $table->index('status_id');
            $table->index('priorite_id');
            $table->index('date_debut');
            $table->index(['departement_id', 'status_id']);
            $table->index(['type_incident_id', 'cause_id']);
            $table->index(['operateur_id', 'superviseur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
