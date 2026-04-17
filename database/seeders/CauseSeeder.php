<?php

namespace Database\Seeders;

use App\Models\Cause;
use App\Models\TypeIncident;
use Illuminate\Database\Seeder;

class CauseSeeder extends Seeder
{
    public function run(): void
    {
        $typeByCode = TypeIncident::pluck('id', 'code');

        $causes = [
            ['code' => 'CABLE_PIOCHE',        'libelle' => 'Câble pioché',                    'type' => 'DISJ_FR'],
            ['code' => 'CEB_TCN',             'libelle' => 'CEB/TCN',                         'type' => 'DARR'],
            ['code' => 'CEB_VRA',             'libelle' => 'CEB/VRA',                         'type' => 'DARL'],
            ['code' => 'CHUTE_ARBRE',         'libelle' => 'Chute d’arbre sur réseau',        'type' => 'MT'],
            ['code' => 'CHUTE_BRANCHES',      'libelle' => 'Chute branches sur réseau',       'type' => 'MT'],
            ['code' => 'COND_ATMO',           'libelle' => 'Conditions atmosphériques',        'type' => 'FUS_MT'],
            ['code' => 'DECL_SYMP',           'libelle' => 'Déclenchement par sympathie',      'type' => 'DISJ_FR'],
            ['code' => 'AUTOEXT',             'libelle' => 'Défaut Autoextincteur',           'type' => 'DISJ_FR'],
            ['code' => 'FUGITIF',             'libelle' => 'Défaut fugitif',                  'type' => 'DISJ_FR'],
            ['code' => 'SEMI_PERM',           'libelle' => 'Défaut Semi Permanent',           'type' => 'DISJ_FR'],
            ['code' => 'BOITE',               'libelle' => 'Défaut Boîte',                    'type' => 'DEF_CELL'],
            ['code' => 'CELL_HTA',            'libelle' => 'Défaut Cellule HTA',              'type' => 'DEF_CELL'],
            ['code' => 'EXT_INTERIEUR',       'libelle' => 'Défaut Ext. intérieures',         'type' => 'DEF_INT'],
            ['code' => 'EXT_EXTERIEUR',       'libelle' => 'Défaut Ext. extérieures',         'type' => 'DEF_EXT'],
            ['code' => 'PLEIN_CABLE',         'libelle' => 'Défaut Plein Câble',              'type' => 'PLEIN_CABLE'],
            ['code' => 'DEF_ENER',            'libelle' => 'Défaut énergétique',              'type' => 'ENER'],
            ['code' => 'ECHEC_MST',           'libelle' => 'Échec de mise sous tension',      'type' => 'ECHEC_MST'],
            ['code' => 'FAUNES_OISEAUX',      'libelle' => 'Faunes (Oiseaux)',                'type' => 'DISJ_FR'],
            ['code' => 'FAUNES_NIDS',         'libelle' => 'Faunes (Nids sur poteaux)',       'type' => 'DISJ_FR'],
            ['code' => 'FAUNES_REPT',         'libelle' => 'Faunes (Reptiles)',               'type' => 'DISJ_FR'],
            ['code' => 'FEUX_BROUSSE',        'libelle' => 'Feux de brousse',                 'type' => 'FEUX'],
            ['code' => 'INCONNUE',            'libelle' => 'Inconnue',                        'type' => 'INCONNU'],
            ['code' => 'ISOLATEUR_CASSE',     'libelle' => 'Isolateur cassé',                 'type' => 'DISJ_FR'],
            ['code' => 'ISOLATEUR_DECRO',     'libelle' => 'Isolateur décroché',              'type' => 'DISJ_FR'],
            ['code' => 'POTEAU_PERCUTE',      'libelle' => 'Poteau percuté',                  'type' => 'DISJ_FR'],
            ['code' => 'RUPTURE_CONDUCTEUR',  'libelle' => 'Rupture de conducteur',           'type' => 'DISJ_FR'],
            ['code' => 'SURCH_ARR',           'libelle' => 'Surcharge arrivée',               'type' => 'SURCH'],
            ['code' => 'SURCH_DEP',           'libelle' => 'Surcharge départ',                'type' => 'SURCH'],
            ['code' => 'TRANS_AV',            'libelle' => 'Transformateur avarié',           'type' => 'TRANS_AV'],
            ['code' => 'VANDALISME',          'libelle' => 'Vandalisme',                      'type' => 'VAND'],
        ];

        $created = 0;
        foreach ($causes as $cause) {
            $typeId = $typeByCode[$cause['type']] ?? null;
            if (! $typeId) {
                continue;
            }
            Cause::updateOrCreate(
                ['code' => $cause['code']],
                [
                    'libelle' => $cause['libelle'],
                    'type_incident_id' => $typeId,
                    'is_active' => true,
                ]
            );
            $created++;
        }

        $this->command?->info("✅ $created causes importées.");
    }
}
