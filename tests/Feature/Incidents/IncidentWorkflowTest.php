<?php

namespace Tests\Feature\Incidents;

use App\Models\Incident;
use App\Models\IncidentAction;
use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use BuildsIncidentContext;

    public function test_operator_can_create_incident_and_duration_is_calculated_when_status_is_final(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $responsable = User::factory()->create();
        $superviseur = User::factory()->create();

        $now = now()->startOfMinute();
        Carbon::setTestNow($now);

        try {
            $response = $this->actingAs($operator)->post(route('incidents.store'), [
                'titre' => 'Incident cloture auto',
                'description' => 'Test creation incident',
                'departement_id' => $context['departement']->id,
                'type_incident_id' => $context['type']->id,
                'cause_id' => $context['cause']->id,
                'status_id' => $context['statusFinal']->id,
                'priorite_id' => $context['priorite']->id,
                'localisation' => 'Poste test',
                'date_debut' => $now->copy()->subHours(2)->format('Y-m-d H:i:s'),
                'date_fin' => null,
                'responsable_id' => $responsable->id,
                'superviseur_id' => $superviseur->id,
                'actions_menees' => 'Actions de test',
                'resolution_summary' => 'Resolution test',
            ]);
        } finally {
            Carbon::setTestNow();
        }

        $response->assertRedirect();

        $incident = Incident::query()->firstOrFail();
        $this->assertSame($operator->id, $incident->operateur_id);
        $this->assertNotNull($incident->date_fin);
        $this->assertNotNull($incident->clotured_at);
        $this->assertSame(120, $incident->duree_minutes);

        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'create',
        ]);
        $this->assertDatabaseHas('logs', [
            'incident_id' => $incident->id,
            'action' => 'create',
        ]);

        $this->assertTrue(IncidentAction::query()->exists());
        $this->assertTrue(Log::query()->exists());
    }

    public function test_operator_cannot_delete_incident_without_delete_permission(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context, ['operateur_id' => $operator->id]);

        $response = $this->actingAs($operator)->delete(route('incidents.destroy', $incident));

        $response->assertForbidden();
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
    }

    public function test_admin_can_delete_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $admin = $this->makeUserWithRole('admin');
        $incident = $this->makeIncident($context, ['operateur_id' => $admin->id]);

        $response = $this->actingAs($admin)->delete(route('incidents.destroy', $incident));

        $response->assertRedirect(route('incidents.index'));
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    public function test_incident_index_filters_by_cause_and_operator(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operatorA = $this->makeUserWithRole('operator', ['name' => 'Operateur A']);
        $operatorB = $this->makeUserWithRole('operator', ['name' => 'Operateur B']);

        $incidentA = $this->makeIncident($context, [
            'code_incident' => 'INC-FILTER-A',
            'cause_id' => $context['cause']->id,
            'operateur_id' => $operatorA->id,
        ]);
        $incidentB = $this->makeIncident($context, [
            'code_incident' => 'INC-FILTER-B',
            'cause_id' => $context['causeAlt']->id,
            'operateur_id' => $operatorB->id,
        ]);

        $response = $this->actingAs($operatorA)->get(route('incidents.index', [
            'cause_id' => $context['cause']->id,
            'operateur_id' => $operatorA->id,
        ]));

        $response->assertOk();
        $response->assertSee($incidentA->code_incident);
        $response->assertDontSee($incidentB->code_incident);
    }
}

