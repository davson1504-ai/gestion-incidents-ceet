<?php

namespace Tests\Unit;

use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentModelTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_incident_recalculate_duration(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $incident = $this->makeIncident($context, [
            'date_debut' => Carbon::parse('2026-04-10 10:00:00'),
            'date_fin' => Carbon::parse('2026-04-10 11:15:00'),
        ]);

        $incident->recalculateDuration();

        $this->assertSame(75, $incident->duree_minutes);
    }

    public function test_incident_scope_filter_filters_by_departement_and_text(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $keep = $this->makeIncident($context, [
            'code_incident' => 'INC-SCOPE-KEEP',
            'titre' => 'Incident critique poste test',
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-SCOPE-DROP',
            'titre' => 'Autre incident',
        ]);

        $result = Incident::query()->filter([
            'departement_id' => $context['departement']->id,
            'q' => 'KEEP',
        ])->pluck('id');

        $this->assertTrue($result->contains($keep->id));
        $this->assertCount(1, $result);
    }

    public function test_incident_resume_resolution_aliases_resolution_summary(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $incident = $this->makeIncident($context, [
            'resolution_summary' => 'Resolution initiale',
        ]);

        $this->assertSame('Resolution initiale', $incident->resume_resolution);

        $incident->resume_resolution = 'Resolution finale';
        $incident->save();
        $incident->refresh();

        $this->assertSame('Resolution finale', $incident->resolution_summary);
    }
}
