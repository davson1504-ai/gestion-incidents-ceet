<?php

namespace Tests\Feature\Catalogues;

use App\Models\Cause;
use App\Models\Departement;
use App\Models\TypeIncident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class CataloguesManagementTest extends TestCase
{
    use RefreshDatabase;
    use BuildsIncidentContext;

    public function test_operator_can_view_catalogues_but_cannot_manage_them(): void
    {
        $this->seedRolesAndPermissions();
        Departement::create([
            'code' => 'DEP-VIEW',
            'nom' => 'Departement Visible',
            'is_active' => true,
        ]);

        $operator = $this->makeUserWithRole('operator');

        $indexResponse = $this->actingAs($operator)->get(route('catalogues.departements.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Departement Visible');

        $createResponse = $this->actingAs($operator)->get(route('catalogues.departements.create'));
        $createResponse->assertForbidden();
    }

    public function test_supervisor_cannot_create_catalogue_entry_without_manage_permission(): void
    {
        $this->seedRolesAndPermissions();
        $supervisor = $this->makeUserWithRole('supervisor');

        $response = $this->actingAs($supervisor)->post(route('catalogues.types.store'), [
            'code' => 'TYPE-SUP',
            'libelle' => 'Type Superviseur',
            'is_active' => true,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('type_incidents', ['code' => 'TYPE-SUP']);
    }

    public function test_admin_can_crud_departement(): void
    {
        $this->seedRolesAndPermissions();
        $admin = $this->makeUserWithRole('admin');

        $store = $this->actingAs($admin)->post(route('catalogues.departements.store'), [
            'code' => 'DEP-CRUD',
            'nom' => 'Departement CRUD',
            'zone' => 'Maritime',
            'poste_source' => 'PS-1',
            'charge_maximale' => 80.50,
            'charge_unite' => 'A',
            'is_active' => 1,
        ]);

        $store->assertRedirect(route('catalogues.departements.index'));
        $departement = Departement::query()->where('code', 'DEP-CRUD')->firstOrFail();
        $this->assertSame('Departement CRUD', $departement->nom);

        $update = $this->actingAs($admin)->put(route('catalogues.departements.update', $departement), [
            'code' => 'DEP-CRUD',
            'nom' => 'Departement CRUD MAJ',
            'zone' => 'Maritime',
            'poste_source' => 'PS-2',
            'charge_maximale' => 99.10,
            'charge_unite' => 'A',
            'is_active' => 1,
        ]);

        $update->assertRedirect(route('catalogues.departements.index'));
        $this->assertDatabaseHas('departements', [
            'id' => $departement->id,
            'nom' => 'Departement CRUD MAJ',
        ]);

        $delete = $this->actingAs($admin)->delete(route('catalogues.departements.destroy', $departement));
        $delete->assertRedirect(route('catalogues.departements.index'));
        $this->assertDatabaseMissing('departements', ['id' => $departement->id]);
    }

    public function test_admin_can_crud_type_and_cause_catalogues(): void
    {
        $this->seedRolesAndPermissions();
        $admin = $this->makeUserWithRole('admin');

        $typeStore = $this->actingAs($admin)->post(route('catalogues.types.store'), [
            'code' => 'TYPE-CRUD',
            'libelle' => 'Type CRUD',
            'description' => 'Type de test',
            'is_active' => 1,
        ]);
        $typeStore->assertRedirect(route('catalogues.types.index'));

        $type = TypeIncident::query()->where('code', 'TYPE-CRUD')->firstOrFail();

        $causeStore = $this->actingAs($admin)->post(route('catalogues.causes.store'), [
            'code' => 'CAUSE-CRUD',
            'libelle' => 'Cause CRUD',
            'description' => 'Cause de test',
            'type_incident_id' => $type->id,
            'is_active' => 1,
        ]);
        $causeStore->assertRedirect(route('catalogues.causes.index'));

        $cause = Cause::query()->where('code', 'CAUSE-CRUD')->firstOrFail();

        $causeUpdate = $this->actingAs($admin)->put(route('catalogues.causes.update', $cause), [
            'code' => 'CAUSE-CRUD',
            'libelle' => 'Cause CRUD MAJ',
            'description' => 'Cause mise a jour',
            'type_incident_id' => $type->id,
            'is_active' => 1,
        ]);
        $causeUpdate->assertRedirect(route('catalogues.causes.index'));
        $this->assertDatabaseHas('causes', [
            'id' => $cause->id,
            'libelle' => 'Cause CRUD MAJ',
        ]);

        $causeDelete = $this->actingAs($admin)->delete(route('catalogues.causes.destroy', $cause));
        $causeDelete->assertRedirect(route('catalogues.causes.index'));
        $this->assertDatabaseMissing('causes', ['id' => $cause->id]);
    }
}

