<?php

namespace Tests\Feature\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_admin_can_see_dashboard(): void
    {
        $this->seedRolesAndPermissions();
        $this->createCatalogContext();
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('kpis');
        $response->assertViewHas('byStatus');
        $response->assertViewHas('topDepart');
    }

    public function test_dashboard_respects_date_filters(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $admin = $this->makeUserWithRole('admin');

        $this->makeIncident($context, [
            'code_incident' => 'INC-DASH-OLD',
            'date_debut' => now()->subDays(12),
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-DASH-NEW',
            'date_debut' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard', [
            'date_from' => now()->subDays(2)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('kpis', function (array $kpis): bool {
            return (int) $kpis['total'] === 1;
        });
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/login');
    }
}
