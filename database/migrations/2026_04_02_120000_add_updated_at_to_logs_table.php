<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            if (!Schema::hasColumn('logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate()->after('created_at');
            }
            if (!Schema::hasColumn('logs', 'created_at')) {
                $table->timestamp('created_at')->useCurrent()->after('incident_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            if (Schema::hasColumn('logs', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
