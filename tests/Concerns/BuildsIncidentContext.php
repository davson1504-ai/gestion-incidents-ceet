<?php

namespace Tests\Concerns;

use App\Models\Cause;
use App\Models\Departement;
use App\Models\Incident;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\TypeIncident;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

trait BuildsIncidentContext
{
    protected function seedRolesAndPermissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function roleName(string $alias): string
    {
        $map = [
            'admin' => ['Administrateur'],
            'supervisor' => ['Superviseur'],
            'operator' => ['Opérateur', 'Operateur'],
        ];

        foreach ($map[$alias] ?? [] as $candidate) {
            $role = Role::query()->where('name', $candidate)->first();
            if ($role) {
                return $role->name;
            }
        }

        $available = Role::query()->pluck('name')->implode(', ');
        $this->fail("Role alias [{$alias}] introuvable. Roles disponibles: {$available}");
    }

    protected function makeUserWithRole(string $roleAlias, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($this->roleName($roleAlias));

        return $user;
    }

    protected function createCatalogContext(): array
    {
        $departement = Departement::create([
            'code' => 'DEP-TEST',
            'nom' => 'Departement Test',
            'zone' => 'Lome',
            'poste_source' => 'PS-Test',
            'is_active' => true,
        ]);

        $type = TypeIncident::create([
            'code' => 'TYPE_TEST',
            'libelle' => 'Type Test',
            'is_active' => true,
        ]);

        $cause = Cause::create([
            'code' => 'CAUSE_TEST',
            'libelle' => 'Cause Test',
            'type_incident_id' => $type->id,
            'is_active' => true,
        ]);

        $causeAlt = Cause::create([
            'code' => 'CAUSE_ALT',
            'libelle' => 'Cause Alternative',
            'type_incident_id' => $type->id,
            'is_active' => true,
        ]);

        $statusOpen = Statut::create([
            'code' => 'EN_COURS',
            'libelle' => 'En cours',
            'ordre' => 1,
            'couleur' => '#ffc107',
            'is_active' => true,
            'is_final' => false,
        ]);

        $statusFinal = Statut::create([
            'code' => 'CLOTURE',
            'libelle' => 'Cloture',
            'ordre' => 2,
            'couleur' => '#6c757d',
            'is_active' => true,
            'is_final' => true,
        ]);

        $priorite = Priorite::create([
            'code' => 'HIGH',
            'libelle' => 'Haute',
            'niveau' => 1,
            'couleur' => '#fd7e14',
            'is_active' => true,
        ]);

        return compact('departement', 'type', 'cause', 'causeAlt', 'statusOpen', 'statusFinal', 'priorite');
    }

    protected function makeIncident(array $context, array $overrides = []): Incident
    {
        $data = array_merge([
            'code_incident' => 'INC-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'titre' => 'Incident de test',
            'description' => 'Description incident de test',
            'departement_id' => $context['departement']->id,
            'type_incident_id' => $context['type']->id,
            'cause_id' => $context['cause']->id,
            'status_id' => $context['statusOpen']->id,
            'priorite_id' => $context['priorite']->id,
            'localisation' => 'Lome',
            'date_debut' => now()->subHour(),
            'date_fin' => null,
            'duree_minutes' => null,
            'operateur_id' => User::factory()->create()->id,
            'responsable_id' => null,
            'superviseur_id' => null,
            'actions_menees' => null,
            'resolution_summary' => null,
            'clotured_at' => null,
        ], $overrides);

        return Incident::create($data);
    }
}
