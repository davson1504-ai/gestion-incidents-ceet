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

    public function test_operator_export_is_limited_to_visible_incidents(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $otherOperator = $this->makeUserWithRole('operator');

        $visibleIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-MINE',
            'operateur_id' => $operator->id,
        ]);

        $hiddenIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-HIDDEN',
            'operateur_id' => $otherOperator->id,
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.export'));
        $content = $response->streamedContent();

        $response->assertOk();
        $this->assertStringContainsString($visibleIncident->code_incident, $content);
        $this->assertStringNotContainsString($hiddenIncident->code_incident, $content);
    }

    public function test_export_preserves_business_order_by_date_debut_desc(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $older = $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-OLDER',
            'operateur_id' => $operator->id,
            'date_debut' => now()->subDays(2),
        ]);

        $newer = $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-NEWER',
            'operateur_id' => $operator->id,
            'date_debut' => now()->subDay(),
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.export'));
        $content = $response->streamedContent();

        $response->assertOk();
        $this->assertLessThan(
            strpos($content, $older->code_incident),
            strpos($content, $newer->code_incident)
        );
    }

    public function test_unauthorized_user_cannot_export(): void
    {
        $this->seedRolesAndPermissions();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('incidents.export'));

        $response->assertForbidden();
    }
}
