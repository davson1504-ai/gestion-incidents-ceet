<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone', 20)->nullable()->unique()->after('email');
            $table->foreignId('departement_id')->nullable()->after('telephone')
                  ->constrained('departements')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('departement_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departement_id']);
            $table->dropColumn(['telephone', 'departement_id', 'is_active']);
        });
    }
};
