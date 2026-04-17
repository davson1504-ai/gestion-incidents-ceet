<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Vide le cache des permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions incidents ──────────────────────────────────────────
        $incidentPerms = [
            'incidents.view',
            'incidents.create',
            'incidents.update',
            'incidents.delete',
        ];

        // ── Permissions catalogues ────────────────────────────────────────
        $cataloguePerms = [
            'catalogues.view',
            'catalogues.manage',
        ];

        $reportingPerms = [
            'reporting.view',
        ];

        $userPerms = [
            'users.view',
            'users.manage',
        ];

        foreach ([...$incidentPerms, ...$cataloguePerms, ...$reportingPerms, ...$userPerms] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Rôles ─────────────────────────────────────────────────────────

        // Administrateur : accès total
        $admin = Role::firstOrCreate(['name' => 'Administrateur', 'guard_name' => 'web']);
        $admin->syncPermissions([...$incidentPerms, ...$cataloguePerms, ...$reportingPerms, ...$userPerms]);

        // Superviseur : lecture + création + modification + lecture catalogues
        $superviseur = Role::firstOrCreate(['name' => 'Superviseur', 'guard_name' => 'web']);
        $superviseur->syncPermissions([
            'incidents.view',
            'incidents.create',
            'incidents.update',
            'catalogues.view',
            'reporting.view',
            'users.view',
            'users.manage',
        ]);

        // Opérateur : lecture + création incidents + rapports
        $operateur = Role::firstOrCreate(['name' => 'Opérateur', 'guard_name' => 'web']);
        $operateur->syncPermissions([
            'incidents.view',
            'incidents.create',
            'catalogues.view',
            'reporting.view',
        ]);

        $this->command->info('✅ Rôles et permissions créés avec succès.');
    }
}
