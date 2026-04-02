<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Permissions regroupées par domaine fonctionnel
        $permissionsByGroup = [
            'incidents' => [
                'incidents.view',
                'incidents.create',
                'incidents.update',
                'incidents.delete',
                'incidents.close',
                'incidents.assign',
            ],
            'actions' => [
                'actions.add',
                'actions.update',
            ],
            'catalogues' => [
                'catalogues.view',
                'catalogues.manage',
            ],
            'reporting' => [
                'reporting.dashboard',
                'reporting.export',
            ],
            'users' => [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'users.assign_roles',
            ],
            'logs' => [
                'logs.view',
            ],
        ];

        $allPermissions = collect($permissionsByGroup)->flatten()->all();

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $rolesWithPermissions = [
            'Administrateur' => $allPermissions,
            'Superviseur' => [
                'incidents.view',
                'incidents.create',
                'incidents.update',
                'incidents.close',
                'incidents.assign',
                'actions.add',
                'actions.update',
                'catalogues.view',
                'reporting.dashboard',
                'reporting.export',
                'logs.view',
            ],
            'Opérateur' => [
                'incidents.view',
                'incidents.create',
                'incidents.update',
                'actions.add',
                'reporting.dashboard',
            ],
        ];

        foreach ($rolesWithPermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }

        $this->command?->info('✅ Rôles et permissions créés/mis à jour avec succès.');
    }
}
