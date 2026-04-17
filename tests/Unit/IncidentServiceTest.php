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
        $service = new IncidentService;

        $code = $service->generateCode();

        $this->assertMatchesRegularExpression('/^INC-\d{8}-[A-Z0-9]{5}$/', $code);
    }

    public function test_sync_duration_sets_duree_minutes_when_status_is_final(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $service = new IncidentService;
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

    public function test_sync_duration_does_nothing_when_status_not_final(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $service = new IncidentService;

        $incident = $this->makeIncident($context, [
            'status_id' => $context['statusOpen']->id,
            'date_fin' => null,
            'duree_minutes' => null,
        ]);

        $service->syncDurationOnClosure($incident);

        $incident->refresh();

        $this->assertNull($incident->date_fin);
        $this->assertNull($incident->duree_minutes);
    }
}
