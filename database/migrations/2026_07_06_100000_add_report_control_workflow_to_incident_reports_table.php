<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('incident_reports')) {
            return;
        }

        if (! Schema::hasColumn('incident_reports', 'operateur_id')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->foreignId('operateur_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('incident_reports', 'statut_rapport')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->string('statut_rapport', 30)
                    ->default('SOUMIS')
                    ->after('observations')
                    ->index();
            });
        }

        if (! Schema::hasColumn('incident_reports', 'motif_refus')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->text('motif_refus')
                    ->nullable()
                    ->after('statut_rapport');
            });
        }

        if (! Schema::hasColumn('incident_reports', 'date_soumission')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->dateTime('date_soumission')
                    ->nullable()
                    ->after('submitted_at')
                    ->index();
            });
        }

        if (! Schema::hasColumn('incident_reports', 'date_validation')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->dateTime('date_validation')
                    ->nullable()
                    ->after('date_soumission')
                    ->index();
            });
        }

        if (! Schema::hasColumn('incident_reports', 'date_refus')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->dateTime('date_refus')
                    ->nullable()
                    ->after('date_validation')
                    ->index();
            });
        }

        if (! Schema::hasColumn('incident_reports', 'valide_par')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->foreignId('valide_par')
                    ->nullable()
                    ->after('date_refus')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('incident_reports', 'refuse_par')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->foreignId('refuse_par')
                    ->nullable()
                    ->after('valide_par')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        DB::table('incident_reports')
            ->whereNull('operateur_id')
            ->update(['operateur_id' => DB::raw('user_id')]);

        DB::table('incident_reports')
            ->whereNull('date_soumission')
            ->update(['date_soumission' => DB::raw('submitted_at')]);

        DB::table('incident_reports')
            ->whereNull('statut_rapport')
            ->update(['statut_rapport' => 'SOUMIS']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('incident_reports')) {
            return;
        }

        Schema::table('incident_reports', function (Blueprint $table): void {
            foreach (['operateur_id', 'valide_par', 'refuse_par'] as $column) {
                if (Schema::hasColumn('incident_reports', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['statut_rapport', 'motif_refus', 'date_soumission', 'date_validation', 'date_refus'] as $column) {
                if (Schema::hasColumn('incident_reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
