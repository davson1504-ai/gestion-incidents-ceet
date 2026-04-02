<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priorites', function (Blueprint $table) {
            $table->id();
            
            $table->string('code', 50)->unique();                    // ex: CRITICAL, HIGH, MEDIUM, LOW
            $table->string('libelle', 100);                          // ex: Critique, Haute, Moyenne, Faible
            $table->text('description')->nullable();
            $table->integer('niveau')->default(3);                   // Plus le nombre est petit, plus c'est prioritaire (1 = Critique)
            $table->string('couleur', 50)->default('#6c757d');       // ex: #dc3545 (rouge), #ffc107 (orange), etc.
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();

            // Index pour optimiser les recherches et tris
            $table->index('code');
            $table->index('niveau');
            $table->index('is_active');
            $table->index(['niveau', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priorites');
    }
};