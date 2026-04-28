<?php

namespace Tests\Feature\Reports;

use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_monthly_aggregation_works_on_the_current_database_driver(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $this->makeIncident($context, [
            'code_incident' => 'INC-MONTH-A',
            'date_debut' => Carbon::parse('2026-04-10 08:00:00'),
            'duree_minutes' => 20,
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-MONTH-B',
            'date_debut' => Carbon::parse('2026-04-21 14:00:00'),
            'duree_minutes' => 40,
        ]);

        $service = app(ReportService::class);
        $rows = $service->monthly([
            'date_from' => '2026-04-01',
            'date_to' => '2026-04-30',
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('2026-04', $rows[0]['mois']);
        $this->assertSame(2, $rows[0]['total']);
        $this->assertEqualsWithDelta(30.0, (float) $rows[0]['duree_moyenne_minutes'], 0.001);
    }
}
