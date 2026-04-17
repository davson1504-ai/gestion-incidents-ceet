<?php

namespace Tests\Feature\Historique;

use App\Models\IncidentAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class HistoriqueWorkflowTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_admin_can_filter_historique_by_action_type_and_query(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $admin = $this->makeUserWithRole('admin');
        $operator = $this->makeUserWithRole('operator');

        $incidentA = $this->makeIncident($context, [
            'code_incident' => 'INC-HISTO-A',
            'operateur_id' => $operator->id,
        ]);
        $incidentB = $this->makeIncident($context, [
            'code_incident' => 'INC-HISTO-B',
            'operateur_id' => $operator->id,
        ]);

        IncidentAction::create([
            'incident_id' => $incidentA->id,
            'user_id' => $admin->id,
            'action_type' => 'update',
            'description' => 'Mise a jour cible',
            'action_date' => Carbon::parse('2026-04-07 10:00:00'),
            'old_values' => ['status_id' => 1],
            'new_values' => ['status_id' => 2],
        ]);

        IncidentAction::create([
            'incident_id' => $incidentB->id,
            'user_id' => $admin->id,
            'action_type' => 'create',
            'description' => 'Creation autre',
            'action_date' => Carbon::parse('2026-04-07 11:00:00'),
            'old_values' => null,
            'new_values' => ['status_id' => 1],
        ]);

        $response = $this->actingAs($admin)->get(route('historique.index', [
            'action_type' => 'update',
            'q' => 'cible',
        ]));

        $response->assertOk();
        $response->assertSee('INC-HISTO-A');
        $response->assertSee('Mise a jour cible');
        $response->assertDontSee('INC-HISTO-B');
    }

    public function test_operator_cannot_access_historique_pages(): void
    {
        $this->seedRolesAndPermissions();
        $operator = $this->makeUserWithRole('operator');

        $response = $this->actingAs($operator)->get(route('historique.index'));

        $response->assertForbidden();
    }

    public function test_supervisor_can_export_historique_as_csv(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->makeIncident($context, [
            'code_incident' => 'INC-HISTO-CSV',
            'operateur_id' => $supervisor->id,
        ]);

        IncidentAction::create([
            'incident_id' => $incident->id,
            'user_id' => $supervisor->id,
            'action_type' => 'create',
            'description' => 'Action export csv',
            'action_date' => now(),
            'old_values' => null,
            'new_values' => ['code' => 'INC-HISTO-CSV'],
        ]);

        $response = $this->actingAs($supervisor)->get(route('historique.export', [
            'format' => 'excel',
        ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'text/csv',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            '.csv',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_admin_can_export_historique_as_pdf(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $admin = $this->makeUserWithRole('admin');
        $incident = $this->makeIncident($context, [
            'code_incident' => 'INC-HISTO-PDF',
            'operateur_id' => $admin->id,
        ]);

        IncidentAction::create([
            'incident_id' => $incident->id,
            'user_id' => $admin->id,
            'action_type' => 'create',
            'description' => 'Action export pdf',
            'action_date' => now(),
            'old_values' => null,
            'new_values' => ['code' => 'INC-HISTO-PDF'],
        ]);

        $response = $this->actingAs($admin)->get(route('historique.export', [
            'format' => 'pdf',
        ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'application/pdf',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            '.pdf',
            (string) $response->headers->get('content-disposition')
        );
    }
}
