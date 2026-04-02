<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');           // On garde le log même si l'utilisateur est supprimé

            $table->string('action', 150);           // ex: login, create_incident, update_incident, cloture_incident, export_report
            $table->string('module', 100);           // ex: auth, incidents, catalogues, reporting, users
            $table->string('target_type', 100)->nullable();   // ex: Incident, Departement, Cause, User
            $table->unsignedBigInteger('target_id')->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Détails supplémentaires (utile pour le débogage et traçabilité fine)
            $table->json('details')->nullable();

            // Relation optionnelle directe avec un incident
            $table->foreignId('incident_id')
                  ->nullable()
                  ->constrained('incidents')
                  ->onDelete('set null');

            $table->timestamp('created_at')->useCurrent();

            // Index pour performances (logs sont souvent consultés par date, utilisateur, action)
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('created_at');
            $table->index(['target_type', 'target_id']);
            $table->index('incident_id');
            $table->index(['user_id', 'created_at']);   // Historique d'un utilisateur
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};