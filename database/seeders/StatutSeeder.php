<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\Statut;
use Illuminate\Database\Seeder;

class StatutSeeder extends Seeder
{
    public function run(): void
    {
        $statuts = [
            ['code' => 'OUVERT', 'libelle' => 'Ouvert', 'description' => 'Incident declare, recu et enregistre', 'ordre' => 1, 'couleur' => '#ef4444', 'is_active' => true, 'is_final' => false],
            ['code' => 'AFFECTE', 'libelle' => 'Affecte', 'description' => 'Incident affecte a un operateur', 'ordre' => 2, 'couleur' => '#f59e0b', 'is_active' => true, 'is_final' => false],
            ['code' => 'EN_COURS', 'libelle' => 'En cours', 'description' => 'Incident pris en charge et en intervention', 'ordre' => 3, 'couleur' => '#0ea5e9', 'is_active' => true, 'is_final' => false],
            ['code' => 'RESOLU', 'libelle' => 'Resolu', 'description' => 'Incident resolu techniquement par l operateur', 'ordre' => 4, 'couleur' => '#22c55e', 'is_active' => true, 'is_final' => false],
            ['code' => 'RAPPORTE', 'libelle' => 'Rapporte', 'description' => 'Rapport d intervention soumis', 'ordre' => 5, 'couleur' => '#14b8a6', 'is_active' => true, 'is_final' => false],
            ['code' => 'VALIDE', 'libelle' => 'Valide', 'description' => 'Resolution validee par le superviseur', 'ordre' => 6, 'couleur' => '#6366f1', 'is_active' => true, 'is_final' => false],
            ['code' => 'CLOTURE', 'libelle' => 'Cloture', 'description' => 'Incident cloture administrativement', 'ordre' => 7, 'couleur' => '#64748b', 'is_active' => true, 'is_final' => true],
            ['code' => 'ANNULE', 'libelle' => 'Annule', 'description' => 'Incident annule', 'ordre' => 8, 'couleur' => '#334155', 'is_active' => true, 'is_final' => true],
        ];

        foreach ($statuts as $statut) {
            Statut::updateOrCreate(['code' => $statut['code']], $statut);
        }

        $enCoursId = Statut::query()->where('code', 'EN_COURS')->value('id');
        $enTraitement = Statut::query()->where('code', 'EN_TRAITEMENT')->first();

        if ($enTraitement && $enCoursId) {
            Incident::query()
                ->where('status_id', $enTraitement->id)
                ->update(['status_id' => $enCoursId]);

            $enTraitement->update([
                'is_active' => false,
                'ordre' => 99,
                'description' => 'Ancien statut conserve pour compatibilite, remplace par EN_COURS.',
            ]);
        }

        $this->command?->info('8 statuts CEET synchronises.');
    }
}
