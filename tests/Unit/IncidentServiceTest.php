<?php

namespace Tests\Unit;

use App\Services\IncidentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentServiceTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_generate_code_format(): void
    {
        $service = app(IncidentService::class);

        $code = $service->generateCode();

        $this->assertMatchesRegularExpression('/^INC-\d{8}-[A-Z0-9]{5}$/', $code);
    }

    public function test_sync_duration_sets_duree_minutes_when_status_is_final(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $service = app(IncidentService::class);
        $now = Carbon::parse('2026-04-10 10:00:00');

        Carbon::setTestNow($now);

        try {
            $incident = $this->makeIncident($context, [
                'status_id' => $context['statusFinal']->id,
                'date_debut' => $now->copy()->subMinutes(60),
                'date_fin' => null,
                'duree_minutes' => null,
            ]);

            $service->syncDurationOnClosure($incident);

            $incident->refresh();

            $this->assertNotNull($incident->date_fin);
            $this->assertSame(60, $incident->duree_minutes);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_assign_incident_updates_responsable_and_logs_action(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $service = app(IncidentService::class);

        $supervisor = $this->makeUserWithRole('supervisor');
        $operator = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context);

        $service->assignIncident($incident, [
            'responsable_id' => $operator->id,
            'commentaire' => 'Affectation service test',
        ], $supervisor);

        $incident->refresh();

        $this->assertSame($operator->id, $incident->responsable_id);
        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'assignation',
        ]);
    }

    public function test_close_incident_sets_final_fields_and_logs_action(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $service = app(IncidentService::class);

        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->makeIncident($context, [
            'date_debut' => now()->subMinutes(90),
            'status_id' => $context['statusValidated']->id,
        ]);

        \App\Models\IncidentReport::create([
            'incident_id' => $incident->id,
            'user_id' => $supervisor->id,
            'actions_realisees' => 'Verification disjoncteur',
            'resultat' => 'Remise en service confirmee',
            'submitted_at' => now(),
            'date_soumission' => now(),
            'statut_rapport' => \App\Models\IncidentReport::STATUS_VALIDATED,
            'date_validation' => now(),
            'valide_par' => $supervisor->id,
        ]);

        $service->closeIncident($incident, [
            'status_id' => $context['statusFinal']->id,
            'resolution_summary' => 'Remise en service confirmee',
            'actions_menees' => 'Verification disjoncteur',
        ], $supervisor);

        $incident->refresh();

        $this->assertNotNull($incident->date_fin);
        $this->assertNotNull($incident->clotured_at);
        $this->assertNotNull($incident->duree_minutes);
        $this->assertSame('Remise en service confirmee', $incident->resolution_summary);

        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'cloture',
        ]);

        $this->assertDatabaseHas('logs', [
            'incident_id' => $incident->id,
            'action' => 'close',
        ]);
    }
}
