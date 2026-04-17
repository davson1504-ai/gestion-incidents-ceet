<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // Vider le cache Spatie avant toute modification
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer la nouvelle permission reporting.view si elle n'existe pas
        Permission::firstOrCreate([
            'name' => 'reporting.view',
            'guard_name' => 'web',
        ]);

        // Mettre à jour le rôle Opérateur : ajouter reporting.view
        $operateur = Role::where('name', 'LIKE', 'Op%rateur')
            ->orWhere('name', 'Opérateur')
            ->orWhere('name', 'Operateur')
            ->first();
        if ($operateur) {
            $operateur->syncPermissions([
                'incidents.view',
                'incidents.create',
                'catalogues.view',
                'reporting.view',
            ]);
        }

        // Mettre à jour le rôle Superviseur : ajouter reporting.view
        $superviseur = Role::where('name', 'LIKE', 'Supervis%')
            ->orWhere('name', 'Superviseur')
            ->first();
        if ($superviseur) {
            $superviseur->syncPermissions([
                'incidents.view',
                'incidents.create',
                'incidents.update',
                'catalogues.view',
                'reporting.view',
                'users.view',
                'users.manage',
            ]);
        }

        // Mettre à jour le rôle Administrateur : ajouter reporting.view
        $admin = Role::where('name', 'LIKE', 'Admin%')
            ->orWhere('name', 'Administrateur')
            ->first();
        if ($admin) {
            $admin->syncPermissions([
                'incidents.view',
                'incidents.create',
                'incidents.update',
                'incidents.delete',
                'catalogues.view',
                'catalogues.manage',
                'reporting.view',
                'users.view',
                'users.manage',
            ]);
        }

        // Vider le cache Spatie après modification
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Remettre les permissions originales à l'Opérateur (rollback)
        $operateur = Role::where('name', 'LIKE', 'Op%rateur')
            ->orWhere('name', 'Opérateur')
            ->orWhere('name', 'Operateur')
            ->first();
        if ($operateur) {
            $operateur->syncPermissions([
                'incidents.view',
                'incidents.create',
                'catalogues.view',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
