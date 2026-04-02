<?php

namespace Database\Seeders;

use App\Models\Priorite;
use Illuminate\Database\Seeder;

class PrioriteSeeder extends Seeder
{
    public function run(): void
    {
        $priorites = [
            ['code' => 'CRITICAL', 'libelle' => 'Critique', 'description' => 'Impact très important sur le réseau', 'niveau' => 1, 'couleur' => '#dc3545'],
            ['code' => 'HIGH',     'libelle' => 'Haute',    'description' => 'Priorité élevée', 'niveau' => 2, 'couleur' => '#fd7e14'],
            ['code' => 'MEDIUM',   'libelle' => 'Moyenne',  'description' => 'Priorité normale', 'niveau' => 3, 'couleur' => '#ffc107'],
            ['code' => 'LOW',      'libelle' => 'Faible',   'description' => 'Faible impact', 'niveau' => 4, 'couleur' => '#28a745'],
        ];

        foreach ($priorites as $p) {
            Priorite::firstOrCreate(['code' => $p['code']], $p);
        }

        $this->command->info('✅ 4 priorités créées avec succès !');
    }
}