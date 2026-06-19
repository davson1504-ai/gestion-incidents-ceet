<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class ExportApiTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_operator_cannot_export_csv(): void
    {
        $this->seedRolesAndPermissions();
        $operator = $this->makeUserWithRole('operator');

        $response = $this->actingAs($operator)->get('/api/v1/exports/incidents.csv');

        $response->assertForbidden();
    }
}
