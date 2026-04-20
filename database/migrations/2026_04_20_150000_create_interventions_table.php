<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type', 100);
            $table->text('description');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('duree_minutes')->nullable();
            $table->string('resultat', 190)->nullable();
            $table->string('statut', 80)->nullable();
            $table->timestamps();

            $table->index(['incident_id', 'started_at'], 'interventions_incident_started_idx');
            $table->index('user_id', 'interventions_user_idx');
            $table->index('action_type', 'interventions_action_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
