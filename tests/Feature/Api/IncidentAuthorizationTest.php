<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentAuthorizationTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_guest_cannot_access_incident_api(): void
    {
        $response = $this->getJson('/api/v1/incidents');

        $response->assertUnauthorized();
    }

    public function test_operator_cannot_assign_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context);

        $response = $this->actingAs($operator)->postJson("/api/v1/incidents/{$incident->id}/assign", [
            'responsable_id' => $operator->id,
        ]);

        $response->assertForbidden();
    }

    public function test_operator_cannot_view_unrelated_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $owner = $this->makeUserWithRole('operator');
        $other = $this->makeUserWithRole('operator');

        $incident = $this->makeIncident($context, ['operateur_id' => $owner->id]);

        $response = $this->actingAs($other)->getJson("/api/v1/incidents/{$incident->id}");

        $response->assertForbidden();
    }

    public function test_supervisor_can_view_all_incidents(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->makeIncident($context);

        $response = $this->actingAs($supervisor)->getJson("/api/v1/incidents/{$incident->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $incident->id);
    }
}
