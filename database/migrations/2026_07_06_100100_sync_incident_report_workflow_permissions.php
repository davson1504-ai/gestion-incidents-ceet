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
        'reports.view',
        'reports.submit',
        'reports.update',
        'reports.validate',
        'reports.reject',
        'incidents.close',
    ];

    /** @var array<int, string> */
    private array $operatorPermissions = [
        'reports.submit',
        'reports.update',
    ];

    /** @var array<int, string> */
    private array $supervisorPermissions = [
        'reports.view',
        'reports.validate',
        'reports.reject',
        'incidents.close',
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
            ->each(function (Role $role): void {
                $role->givePermissionTo($this->permissions);
            });

        $this->roles(['Superviseur', 'superviseur', 'SUPERVISEUR'], ['Super%'])
            ->each(function (Role $role): void {
                $role->givePermissionTo($this->supervisorPermissions);
            });

        $this->roles(['Opérateur', 'OpÃ©rateur', 'Operateur', 'operateur', 'OPERATEUR', 'OPÉRATEUR'], ['Op%rateur%', 'Operateur%'])
            ->each(function (Role $role): void {
                $role->givePermissionTo($this->operatorPermissions);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->roles(['Opérateur', 'OpÃ©rateur', 'Operateur', 'operateur', 'OPERATEUR', 'OPÉRATEUR'], ['Op%rateur%', 'Operateur%'])
            ->each(fn (Role $role) => $role->revokePermissionTo($this->operatorPermissions));

        $this->roles(['Superviseur', 'superviseur', 'SUPERVISEUR'], ['Super%'])
            ->each(fn (Role $role) => $role->revokePermissionTo(['reports.view', 'reports.validate', 'reports.reject']));

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
