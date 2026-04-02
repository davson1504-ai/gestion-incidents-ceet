<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            StatutSeeder::class,
            PrioriteSeeder::class,
            TypeIncidentSeeder::class,
            DepartementSeeder::class,
            CauseSeeder::class,
            AdminUserSeeder::class,
        ]);

        $this->command?->info('🎉 Base de données initialisée avec succès !');
    }
}
