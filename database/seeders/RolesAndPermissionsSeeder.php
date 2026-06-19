<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'incidents.view',
            'incidents.view.assigned',
            'incidents.create',
            'incidents.update',
            'incidents.delete',
            'incidents.assign',
            'incidents.take',
            'incidents.resolve',
            'incidents.report',
            'incidents.validate',
            'incidents.close',
            'incidents.export',
            'catalogues.view',
            'catalogues.manage',
            'logs.view',
            'roles.manage',
            'system.view',
            'reporting.view',
            'reporting.export',
            'users.view',
            'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = $this->resolveOrCreateRole(['Administrateur', 'admin']);
        $superviseur = $this->resolveOrCreateRole(['Superviseur', 'superviseur']);
        $operateur = $this->resolveOrCreateRole(['Opérateur', 'OpÃ©rateur', 'Operateur', 'operateur']);

        $admin->syncPermissions([
            'users.view',
            'users.manage',
            'roles.manage',
            'catalogues.view',
            'catalogues.manage',
            'logs.view',
            'system.view',
            'incidents.view',
            'incidents.delete',
            'reporting.view',
            'reporting.export',
        ]);

        $superviseur->syncPermissions([
            'incidents.view',
            'incidents.create',
            'incidents.update',
            'incidents.assign',
            'incidents.validate',
            'incidents.close',
            'incidents.export',
            'reporting.view',
            'reporting.export',
        ]);

        $operateur->syncPermissions([
            'incidents.view',
            'incidents.view.assigned',
            'incidents.take',
            'incidents.resolve',
            'incidents.report',
        ]);

        $this->syncAliases($admin, ['admin']);
        $this->syncAliases($superviseur, ['superviseur']);
        $this->syncAliases($operateur, ['Operateur', 'operateur']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Roles et permissions CEET synchronises.');
    }

    private function resolveOrCreateRole(array $names): Role
    {
        $role = Role::query()
            ->whereIn('name', $names)
            ->where('guard_name', 'web')
            ->first();

        if ($role) {
            return $role;
        }

        return Role::create([
            'name' => Collection::make($names)->first(),
            'guard_name' => 'web',
        ]);
    }

    private function syncAliases(Role $sourceRole, array $aliases): void
    {
        $permissions = $sourceRole->permissions->pluck('name')->all();

        foreach ($aliases as $alias) {
            $role = Role::firstOrCreate([
                'name' => $alias,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }
    }
}
