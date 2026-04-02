<?php

namespace Database\Seeders;

use App\Models\TypeIncident;
use Illuminate\Database\Seeder;

class TypeIncidentSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'DISJ_FR',   'libelle' => 'Disjonction Franche',          'description' => null],
            ['code' => 'DARR',      'libelle' => 'DARR',                         'description' => 'Déclenchement automatique régulé réseau'],
            ['code' => 'DARL',      'libelle' => 'DARL',                         'description' => 'Déclenchement automatique régulé ligne'],
            ['code' => 'MT',        'libelle' => 'Manque de tension',            'description' => null],
            ['code' => 'BLACKOUT',  'libelle' => 'Black Out',                    'description' => null],
            ['code' => 'FUS_MT',    'libelle' => 'Fusion fusible MT',            'description' => null],
            ['code' => 'DEF_CELL',  'libelle' => 'Défaut Cellule HTA',           'description' => null],
            ['code' => 'DEF_EXT',   'libelle' => 'Défaut Extérieur',             'description' => 'Défaut extérieur réseau'],
            ['code' => 'DEF_INT',   'libelle' => 'Défaut Intérieur',             'description' => 'Défaut intérieur'],
            ['code' => 'PLEIN_CABLE','libelle' => 'Défaut Plein Câble',          'description' => null],
            ['code' => 'ENER',      'libelle' => 'Défaut énergétique',           'description' => null],
            ['code' => 'ECHEC_MST', 'libelle' => 'Échec mise sous tension',      'description' => null],
            ['code' => 'FEUX',      'libelle' => 'Feux de brousse',              'description' => null],
            ['code' => 'SURCH',     'libelle' => 'Surcharge',                    'description' => null],
            ['code' => 'TRANS_AV',  'libelle' => 'Transformateur avarié',        'description' => null],
            ['code' => 'VAND',      'libelle' => 'Vandalisme',                   'description' => null],
            ['code' => 'INCONNU',   'libelle' => 'Inconnue',                     'description' => null],
        ];

        foreach ($types as $t) {
            TypeIncident::updateOrCreate(['code' => $t['code']], $t);
        }

        $this->command?->info('✅ '.count($types).' types d’incident importés.');
    }
}
