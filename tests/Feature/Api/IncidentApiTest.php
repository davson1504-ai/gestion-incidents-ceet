<?php

namespace Tests\Feature\Api;

use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentApiTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_api_can_create_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $response = $this->actingAs($operator)->postJson('/api/v1/incidents', [
            'titre' => 'Coupure haute tension Agoe',
            'description' => 'Declenchement cellule HTA',
            'departement_id' => $context['departement']->id,
            'type_incident_id' => $context['type']->id,
            'cause_id' => $context['cause']->id,
            'localisation' => 'Poste Agoe 20KV',
            'date_debut' => now()->subMinutes(30)->format('Y-m-d H:i:s'),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.departement_id', $context['departement']->id);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'code_incident', 'titre', 'status_id', 'priorite_id'],
        ]);

        $this->assertDatabaseHas('incidents', [
            'titre' => 'Coupure haute tension Agoe',
            'operateur_id' => $operator->id,
        ]);
    }

    public function test_api_filters_incidents_by_date_and_catalogues(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $incidentTarget = $this->makeIncident($context, [
            'code_incident' => 'INC-FILTRE-TARGET',
            'operateur_id' => $operator->id,
            'date_debut' => Carbon::parse('2026-04-10 08:00:00'),
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-FILTRE-OUTSIDE-DATE',
            'operateur_id' => $operator->id,
            'date_debut' => Carbon::parse('2026-04-01 08:00:00'),
        ]);

        $response = $this->actingAs($operator)->getJson('/api/v1/incidents?'
            .http_build_query([
                'date_from' => '2026-04-09',
                'date_to' => '2026-04-11',
                'departement_id' => $context['departement']->id,
                'type_incident_id' => $context['type']->id,
                'cause_id' => $context['cause']->id,
                'operateur_id' => $operator->id,
                'q' => 'TARGET',
            ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.code_incident', $incidentTarget->code_incident);
    }

    public function test_api_can_assign_and_close_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');
        $operator = $this->makeUserWithRole('operator');

        $incident = $this->makeIncident($context, [
            'operateur_id' => $operator->id,
            'date_debut' => now()->subHours(2),
            'status_id' => $context['statusOpen']->id,
        ]);

        $assignResponse = $this->actingAs($supervisor)->postJson("/api/v1/incidents/{$incident->id}/assign", [
            'responsable_id' => $operator->id,
            'commentaire' => 'Prise en charge terrain immediate',
        ]);

        $assignResponse->assertOk();
        $assignResponse->assertJsonPath('data.responsable_id', $operator->id);

        $closeResponse = $this->actingAs($supervisor)->postJson("/api/v1/incidents/{$incident->id}/close", [
            'status_id' => $context['statusFinal']->id,
            'resolution_summary' => 'Defaut localise et alimentation retablie',
            'actions_menees' => 'Manoeuvre cellule + verification protections',
        ]);

        $closeResponse->assertOk();
        $closeResponse->assertJsonPath('success', true);

        $incident->refresh();
        $this->assertNotNull($incident->date_fin);
        $this->assertNotNull($incident->duree_minutes);

        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'assignation',
        ]);

        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'cloture',
        ]);
    }

    public function test_operator_can_add_intervention_on_assigned_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');
        $operator = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context, [
            'responsable_id' => $operator->id,
            'superviseur_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($operator)->postJson("/api/v1/incidents/{$incident->id}/interventions", [
            'action_type' => 'diagnostic',
            'description' => 'Controle des protections au poste source',
            'started_at' => now()->subMinutes(35)->format('Y-m-d H:i:s'),
            'ended_at' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
            'resultat' => 'Anomalie localisee',
            'statut' => 'terminee',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.action_type', 'diagnostic');

        $this->assertDatabaseHas('interventions', [
            'incident_id' => $incident->id,
            'user_id' => $operator->id,
            'action_type' => 'diagnostic',
        ]);
    }
}
