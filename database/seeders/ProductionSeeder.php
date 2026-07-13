<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartementSeeder::class,
            TypeIncidentSeeder::class,
            CauseSeeder::class,
            StatutSeeder::class,
            PrioriteSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
