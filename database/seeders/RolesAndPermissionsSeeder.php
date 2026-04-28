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
            'incidents.create',
            'incidents.update',
            'incidents.delete',
            'incidents.assign',
            'incidents.close',
            'incidents.export',
            'catalogues.view',
            'catalogues.manage',
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
        $operateur = $this->resolveOrCreateRole(['Opérateur', 'Operateur', 'operateur']);

        $admin->syncPermissions($permissions);

        $superviseur->syncPermissions([
            'incidents.view',
            'incidents.create',
            'incidents.update',
            'incidents.assign',
            'incidents.close',
            'incidents.export',
            'catalogues.view',
            'reporting.view',
            'reporting.export',
            'users.view',
            'users.manage',
        ]);

        $operateur->syncPermissions([
            'incidents.view',
            'incidents.create',
            'incidents.export',
            'catalogues.view',
            'reporting.view',
            'reporting.export',
        ]);

        $this->syncAliases($admin, ['admin']);
        $this->syncAliases($superviseur, ['superviseur']);
        $this->syncAliases($operateur, ['Operateur', 'operateur']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Roles et permissions synchronises.');
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
