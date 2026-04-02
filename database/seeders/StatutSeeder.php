<?php

namespace Database\Seeders;

use App\Models\Statut;
use Illuminate\Database\Seeder;

class StatutSeeder extends Seeder
{
    public function run(): void
    {
        $statuts = [
            [
                'code' => 'EN_COURS',
                'libelle' => 'En cours',
                'description' => 'Incident déclaré mais pas encore pris en charge',
                'ordre' => 1,
                'couleur' => '#ffc107',
                'is_active' => true,
                'is_final' => false,
            ],
            [
                'code' => 'EN_TRAITEMENT',
                'libelle' => 'En traitement',
                'description' => 'Équipe terrain est intervenue',
                'ordre' => 2,
                'couleur' => '#17a2b8',
                'is_active' => true,
                'is_final' => false,
            ],
            [
                'code' => 'RESOLU',
                'libelle' => 'Résolu',
                'description' => 'Incident résolu techniquement',
                'ordre' => 3,
                'couleur' => '#28a745',
                'is_active' => true,
                'is_final' => true,
            ],
            [
                'code' => 'CLOTURE',
                'libelle' => 'Clôturé',
                'description' => 'Incident clôturé administrativement',
                'ordre' => 4,
                'couleur' => '#6c757d',
                'is_active' => true,
                'is_final' => true,
            ],
        ];

        foreach ($statuts as $statut) {
            Statut::updateOrCreate(
                ['code' => $statut['code']],
                $statut
            );
        }

        $this->command->info('✅ 4 statuts créés avec succès !');
    }
}