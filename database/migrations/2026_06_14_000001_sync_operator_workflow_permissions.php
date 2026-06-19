<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $operatorPermissions = [
        'incidents.view.assigned',
        'incidents.take',
        'incidents.resolve',
        'incidents.report',
    ];

    /** @var array<int, string> */
    private array $previousOperatorPermissions = [
        'incidents.view',
        'incidents.create',
        'catalogues.view',
        'reporting.view',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->operatorPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->operatorRoles()->each(function (Role $role): void {
            $role->syncPermissions($this->operatorPermissions);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->previousOperatorPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->operatorRoles()->each(function (Role $role): void {
            $role->syncPermissions($this->previousOperatorPermissions);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function operatorRoles()
    {
        $names = [
            'Opérateur',
            'OpÃ©rateur',
            'Operateur',
            'operateur',
            'OPERATEUR',
            'OPÉRATEUR',
        ];

        return Role::query()
            ->where('guard_name', 'web')
            ->where(function (Builder $query) use ($names): void {
                $query
                    ->whereIn('name', $names)
                    ->orWhere('name', 'like', 'Op%rateur%')
                    ->orWhere('name', 'like', 'Operateur%');
            })
            ->get();
    }
};
