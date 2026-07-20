<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class OfflinePresentationTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $externalHosts = [
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'fonts.bunny.net',
        'lh3.googleusercontent.com',
        'cdn.jsdelivr.net',
        'cdnjs.cloudflare.com',
        'unpkg.com',
    ];

    public function test_essential_presentation_pages_have_no_critical_external_assets(): void
    {
        $this->seedRolesAndPermissions();
        $this->createCatalogContext();

        $admin = $this->makeUserWithRole('admin');
        $supervisor = $this->makeUserWithRole('supervisor');
        $operator = $this->makeUserWithRole('operator');

        $this->assertOfflineResponse($this->get(route('login')));

        foreach (['dashboard', 'catalogues.index', 'reports.index', 'system.status'] as $route) {
            $this->assertOfflineResponse($this->actingAs($admin)->get(route($route)));
        }

        foreach (['dashboard', 'incidents.index', 'incidents.en-cours', 'incidents.create'] as $route) {
            $this->assertOfflineResponse($this->actingAs($supervisor)->get(route($route)));
        }

        foreach (['dashboard', 'incidents.mine', 'incidents.en-cours'] as $route) {
            $this->assertOfflineResponse($this->actingAs($operator)->get(route($route)));
        }
    }

    private function assertOfflineResponse(TestResponse $response): void
    {
        $response->assertOk();

        $html = $response->getContent();

        foreach ($this->externalHosts as $host) {
            $this->assertStringNotContainsString($host, $html, "Ressource externe critique detectee: {$host}");
        }
    }
}
