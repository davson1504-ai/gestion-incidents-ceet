<?php

namespace Tests\Feature\Incidents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentExportTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_export_returns_csv_file(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-001',
            'operateur_id' => $operator->id,
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.export'));
        $content = $response->streamedContent();

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Code;Titre;Département;Statut;Priorité;Type;Cause;Début;Fin;', $content);
        $this->assertStringContainsString('Opérateur', $content);
    }

    public function test_export_respects_status_filter(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-OPEN',
            'status_id' => $context['statusOpen']->id,
            'operateur_id' => $operator->id,
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-CLOSED',
            'status_id' => $context['statusFinal']->id,
            'date_fin' => now(),
            'duree_minutes' => 30,
            'operateur_id' => $operator->id,
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.export', [
            'status_id' => $context['statusOpen']->id,
        ]));

        $content = trim($response->streamedContent());
        $lines = collect(preg_split('/\r\n|\n|\r/', $content))->filter();
        $dataLines = $lines->slice(1)->values();

        $response->assertOk();
        $this->assertCount(1, $dataLines);
        $this->assertStringContainsString('INC-EXPORT-OPEN', $dataLines->first());
        $this->assertStringNotContainsString('INC-EXPORT-CLOSED', $content);
    }

    public function test_unauthorized_user_cannot_export(): void
    {
        $this->seedRolesAndPermissions();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('incidents.export'));

        $response->assertForbidden();
    }
}