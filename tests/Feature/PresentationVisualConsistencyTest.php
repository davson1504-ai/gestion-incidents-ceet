<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class PresentationVisualConsistencyTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_login_uses_french_branding_without_inline_css(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('Gestion des incidents électriques')
            ->assertDontSee('Electrical Management')
            ->assertDontSee('<style', false);
    }

    public function test_role_menus_use_clear_french_labels(): void
    {
        $this->seedRolesAndPermissions();
        $this->createCatalogContext();

        $admin = $this->makeUserWithRole('admin');
        $supervisor = $this->makeUserWithRole('supervisor');
        $operator = $this->makeUserWithRole('operator');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tous les incidents')
            ->assertSee('Utilisateurs')
            ->assertSee('Statut système')
            ->assertSee('Catalogues')
            ->assertSee('Rapports')
            ->assertDontSee('Electrical Management');

        $this->actingAs($supervisor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tous les incidents')
            ->assertSee('Suivi en cours')
            ->assertSee('Déclarer un incident')
            ->assertDontSee('System Status');

        $this->actingAs($operator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mes incidents')
            ->assertSee('Suivi en cours')
            ->assertDontSee('System Status');
    }
}
