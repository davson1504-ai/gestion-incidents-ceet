<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            
            $table->string('code', 50)->unique();                    // ex: LOM01, KARA02, DAPA03
            $table->string('nom', 150);                              // ex: Lomé Centre, Kara, Dapaong
            $table->string('zone', 150)->nullable();                 // ex: Région Maritime, Région des Savanes
            $table->string('poste_source', 150)->nullable();         // Poste source principal
            $table->text('description')->nullable();
            
            $table->integer('charge_maximale_kw')->nullable();       // Charge maximale en kW (utile pour stats)
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();

            // Index pour accélérer les recherches
            $table->index('nom');
            $table->index('zone');
            $table->index('is_active');
            $table->index(['zone', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};