<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $permissions = [
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

    /** @var array<int, string> */
    private array $supervisorPermissions = [
        'incidents.view',
        'incidents.create',
        'incidents.update',
        'incidents.assign',
        'incidents.validate',
        'incidents.close',
        'incidents.export',
        'system.view',
        'logs.view',
        'reporting.view',
        'reporting.export',
    ];

    /** @var array<int, string> */
    private array $operatorPermissions = [
        'incidents.view.assigned',
        'incidents.take',
        'incidents.resolve',
        'incidents.report',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->roles(['Administrateur', 'admin', 'ADMINISTRATEUR'], ['Admin%'])
            ->each(fn (Role $role) => $role->syncPermissions($this->permissions));

        $this->roles(['Superviseur', 'superviseur', 'SUPERVISEUR'], ['Super%'])
            ->each(fn (Role $role) => $role->syncPermissions($this->supervisorPermissions));

        $this->roles(['Opérateur', 'OpÃ©rateur', 'Operateur', 'operateur', 'OPERATEUR', 'OPÉRATEUR'], ['Op%rateur%', 'Operateur%'])
            ->each(fn (Role $role) => $role->syncPermissions($this->operatorPermissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->roles(['Administrateur', 'admin', 'ADMINISTRATEUR'], ['Admin%'])
            ->each(fn (Role $role) => $role->syncPermissions([
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
            ]));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @param array<int, string> $names @param array<int, string> $likePatterns */
    private function roles(array $names, array $likePatterns = [])
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where(function (Builder $query) use ($names, $likePatterns): void {
                $query->whereIn('name', $names);

                foreach ($likePatterns as $pattern) {
                    $query->orWhere('name', 'like', $pattern);
                }
            })
            ->get();
    }
};
