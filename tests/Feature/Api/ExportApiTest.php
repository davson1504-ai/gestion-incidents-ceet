<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class ExportApiTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_operator_csv_export_is_limited_to_visible_incidents(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $otherOperator = $this->makeUserWithRole('operator');

        $visibleIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-API-EXPORT-MINE',
            'operateur_id' => $operator->id,
        ]);

        $hiddenIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-API-EXPORT-HIDDEN',
            'operateur_id' => $otherOperator->id,
        ]);

        $response = $this->actingAs($operator)->get('/api/v1/exports/incidents.csv');
        $content = $response->streamedContent();

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString($visibleIncident->code_incident, $content);
        $this->assertStringNotContainsString($hiddenIncident->code_incident, $content);
    }
}
