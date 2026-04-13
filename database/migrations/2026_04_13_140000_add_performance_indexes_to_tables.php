<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (! $this->hasIndex('sessions', 'sessions_last_activity_index')) {
                $table->index('last_activity', 'sessions_last_activity_index');
            }
        });

        Schema::table('incidents', function (Blueprint $table) {
            if (! $this->hasIndex('incidents', 'incidents_priorite_date_index')) {
                $table->index(['priorite_id', 'date_debut'], 'incidents_priorite_date_index');
            }

            if (! $this->hasIndex('incidents', 'incidents_dates_index')) {
                $table->index(['date_debut', 'date_fin'], 'incidents_dates_index');
            }
        });

        Schema::table('incident_actions', function (Blueprint $table) {
            if (! $this->hasIndex('incident_actions', 'ia_incident_date_index')) {
                $table->index(['incident_id', 'action_date'], 'ia_incident_date_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if ($this->hasIndex('sessions', 'sessions_last_activity_index')) {
                $table->dropIndex('sessions_last_activity_index');
            }
        });

        Schema::table('incidents', function (Blueprint $table) {
            if ($this->hasIndex('incidents', 'incidents_priorite_date_index')) {
                $table->dropIndex('incidents_priorite_date_index');
            }

            if ($this->hasIndex('incidents', 'incidents_dates_index')) {
                $table->dropIndex('incidents_dates_index');
            }
        });

        Schema::table('incident_actions', function (Blueprint $table) {
            if ($this->hasIndex('incident_actions', 'ia_incident_date_index')) {
                $table->dropIndex('ia_incident_date_index');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

            return count($indexes) > 0;
        } catch (\Throwable) {
            return false;
        }
    }
};
