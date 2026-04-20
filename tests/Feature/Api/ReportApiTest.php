<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_report_overview_and_breakdowns(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $supervisor = $this->makeUserWithRole('supervisor');

        $this->makeIncident($context, [
            'date_debut' => Carbon::parse('2026-04-08 08:00:00'),
            'duree_minutes' => 40,
        ]);

        $this->makeIncident($context, [
            'date_debut' => Carbon::parse('2026-04-08 13:00:00'),
            'cause_id' => $context['causeAlt']->id,
            'duree_minutes' => 65,
        ]);

        $overview = $this->actingAs($supervisor)->getJson('/api/v1/reports/overview?date_from=2026-04-01&date_to=2026-04-30');
        $overview->assertOk();
        $overview->assertJsonPath('data.total_incidents', 2);

        $byType = $this->actingAs($supervisor)->getJson('/api/v1/reports/by-type');
        $byType->assertOk();
        $byType->assertJsonPath('data.0.total', 2);

        $daily = $this->actingAs($supervisor)->getJson('/api/v1/reports/daily');
        $daily->assertOk();
        $daily->assertJsonPath('data.0.total', 2);
    }

    public function test_report_exports_csv_and_pdf(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $supervisor = $this->makeUserWithRole('supervisor');

        $this->makeIncident($context, [
            'code_incident' => 'INC-EXPORT-API-001',
            'duree_minutes' => 35,
        ]);

        $csvResponse = $this->actingAs($supervisor)->get('/api/v1/exports/incidents.csv');
        $csvResponse->assertOk();
        $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('INC-EXPORT-API-001', $csvResponse->streamedContent());

        $pdfResponse = $this->actingAs($supervisor)->get('/api/v1/exports/incidents.pdf');
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}
