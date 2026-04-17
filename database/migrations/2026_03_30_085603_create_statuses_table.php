<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();                    // ex: EN_COURS, EN_TRAITEMENT, RESOLU, CLOTURE
            $table->string('libelle', 100);                          // ex: En cours, En traitement, Résolu, Clôturé
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);                    // Pour ordonner le workflow (1, 2, 3...)
            $table->string('couleur', 50)->default('#6c757d');       // Couleur pour affichage (ex: #28a745, #ffc107, #dc3545)
            $table->boolean('is_active')->default(true);
            $table->boolean('is_final')->default(false);             // Pour savoir si c'est un statut de fin (ex: Résolu, Clôturé)

            $table->timestamps();

            // Index pour optimiser les recherches et tris
            $table->index('code');
            $table->index('ordre');
            $table->index('is_active');
            $table->index('is_final');
            $table->index(['is_active', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
