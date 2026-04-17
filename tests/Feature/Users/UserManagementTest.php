<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_supervisor_can_create_user_and_assign_role(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $manager = $this->makeUserWithRole('supervisor');
        $operatorRole = $this->roleName('operator');

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Nouvel Operateur',
            'email' => 'new.operator@ceet.test',
            'telephone' => '90909090',
            'departement_id' => $context['departement']->id,
            'role' => $operatorRole,
            'is_active' => '1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));

        $created = User::query()->where('email', 'new.operator@ceet.test')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $created->password));
        $this->assertTrue($created->is_active);
        $this->assertSame($context['departement']->id, $created->departement_id);
        $this->assertTrue($created->hasRole($operatorRole));
    }

    public function test_operator_cannot_access_users_index(): void
    {
        $this->seedRolesAndPermissions();
        $operator = $this->makeUserWithRole('operator');

        $response = $this->actingAs($operator)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_destroy_deactivates_user_when_history_exists(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $manager = $this->makeUserWithRole('supervisor');
        $target = $this->makeUserWithRole('operator', ['is_active' => true]);

        $this->makeIncident($context, [
            'operateur_id' => $target->id,
            'code_incident' => 'INC-HISTORY-USER',
        ]);

        $response = $this->actingAs($manager)->delete(route('users.destroy', $target));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => false,
        ]);
    }

    public function test_destroy_removes_user_without_history(): void
    {
        $this->seedRolesAndPermissions();

        $manager = $this->makeUserWithRole('supervisor');
        $target = $this->makeUserWithRole('operator');

        $response = $this->actingAs($manager)->delete(route('users.destroy', $target));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_user_cannot_delete_own_account_from_users_module(): void
    {
        $this->seedRolesAndPermissions();

        $manager = $this->makeUserWithRole('supervisor');

        $response = $this->actingAs($manager)->delete(route('users.destroy', $manager));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $manager->id]);
    }

    public function test_users_index_filters_by_role_active_flag_and_query(): void
    {
        $this->seedRolesAndPermissions();
        $manager = $this->makeUserWithRole('admin');

        $operatorRole = $this->roleName('operator');
        $this->makeUserWithRole('operator', [
            'name' => 'Alpha Ops',
            'is_active' => true,
        ]);
        $this->makeUserWithRole('operator', [
            'name' => 'Beta Ops',
            'is_active' => false,
        ]);
        $this->makeUserWithRole('supervisor', [
            'name' => 'Gamma Supervisor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)->get(route('users.index', [
            'q' => 'Alpha',
            'role' => $operatorRole,
            'is_active' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Alpha Ops');
        $response->assertDontSee('Beta Ops');
        $response->assertDontSee('Gamma Supervisor');
    }
}
