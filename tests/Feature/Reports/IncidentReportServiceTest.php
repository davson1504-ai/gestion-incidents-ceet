<?php

namespace Tests\Feature\Reports;

use App\Services\IncidentReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentReportServiceTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_daily_data_aggregates_only_the_selected_day(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $this->makeIncident($context, [
            'code_incident' => 'INC-DAY-A',
            'date_debut' => Carbon::parse('2026-04-07 08:00:00'),
            'duree_minutes' => 60,
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-DAY-B',
            'date_debut' => Carbon::parse('2026-04-07 11:00:00'),
            'cause_id' => $context['causeAlt']->id,
            'duree_minutes' => 30,
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-OUTSIDE-DAY',
            'date_debut' => Carbon::parse('2026-04-08 08:00:00'),
            'duree_minutes' => 45,
        ]);

        $service = app(IncidentReportService::class);
        $data = $service->dailyData(Carbon::parse('2026-04-07'));

        $this->assertSame(2, $data['total']);
        $this->assertEqualsWithDelta(45.0, (float) $data['avgDuration'], 0.001);
        $this->assertSame('day', $data['granularity']);
        $this->assertCount(1, $data['timeseries']);

        $byCause = Collection::make($data['byCause'])->keyBy('label');
        $this->assertSame(1, $byCause->get('Cause Test')['total']);
        $this->assertSame(1, $byCause->get('Cause Alternative')['total']);
    }

    public function test_monthly_data_excludes_incidents_from_other_months(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $this->makeIncident($context, [
            'code_incident' => 'INC-APRIL',
            'date_debut' => Carbon::parse('2026-04-10 14:00:00'),
            'duree_minutes' => 20,
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-MARCH',
            'date_debut' => Carbon::parse('2026-03-30 10:00:00'),
            'duree_minutes' => 70,
        ]);

        $service = app(IncidentReportService::class);
        $data = $service->monthlyData(Carbon::createFromDate(2026, 4, 1));

        $this->assertSame(1, $data['total']);
        $this->assertSame('month', $data['granularity']);
        $this->assertSame('INC-APRIL', $data['incidents']->first()->code_incident);
    }
}
