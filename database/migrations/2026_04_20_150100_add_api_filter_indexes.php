<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('causes', function (Blueprint $table) {
            if (! Schema::hasIndex('causes', 'causes_type_libelle_idx')) {
                $table->index(['type_incident_id', 'libelle'], 'causes_type_libelle_idx');
            }
        });

        Schema::table('incidents', function (Blueprint $table) {
            if (! Schema::hasIndex('incidents', 'incidents_filters_core_idx')) {
                $table->index(
                    ['departement_id', 'type_incident_id', 'cause_id', 'status_id', 'date_debut'],
                    'incidents_filters_core_idx'
                );
            }

            if (! Schema::hasIndex('incidents', 'incidents_operator_status_date_idx')) {
                $table->index(['operateur_id', 'status_id', 'date_debut'], 'incidents_operator_status_date_idx');
            }

            if (! Schema::hasIndex('incidents', 'incidents_responsable_status_idx')) {
                $table->index(['responsable_id', 'status_id'], 'incidents_responsable_status_idx');
            }

            if (! Schema::hasIndex('incidents', 'incidents_superviseur_status_idx')) {
                $table->index(['superviseur_id', 'status_id'], 'incidents_superviseur_status_idx');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasIndex('users', 'users_active_departement_idx')) {
                $table->index(['is_active', 'departement_id'], 'users_active_departement_idx');
            }
        });

        Schema::table('logs', function (Blueprint $table) {
            if (! Schema::hasIndex('logs', 'logs_module_action_created_idx')) {
                $table->index(['module', 'action', 'created_at'], 'logs_module_action_created_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('causes', function (Blueprint $table) {
            if (Schema::hasIndex('causes', 'causes_type_libelle_idx')) {
                $table->dropIndex('causes_type_libelle_idx');
            }
        });

        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasIndex('incidents', 'incidents_filters_core_idx')) {
                $table->dropIndex('incidents_filters_core_idx');
            }

            if (Schema::hasIndex('incidents', 'incidents_operator_status_date_idx')) {
                $table->dropIndex('incidents_operator_status_date_idx');
            }

            if (Schema::hasIndex('incidents', 'incidents_responsable_status_idx')) {
                $table->dropIndex('incidents_responsable_status_idx');
            }

            if (Schema::hasIndex('incidents', 'incidents_superviseur_status_idx')) {
                $table->dropIndex('incidents_superviseur_status_idx');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_active_departement_idx')) {
                $table->dropIndex('users_active_departement_idx');
            }
        });

        Schema::table('logs', function (Blueprint $table) {
            if (Schema::hasIndex('logs', 'logs_module_action_created_idx')) {
                $table->dropIndex('logs_module_action_created_idx');
            }
        });
    }
};
