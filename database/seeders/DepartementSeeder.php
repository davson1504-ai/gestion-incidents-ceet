<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartementSeeder extends Seeder
{
    /**
     * Données fournies (74 départs) : nom, périmètre d’exploitation, poste de répartition.
     */
    private array $rows = [
        ['Direction EAMAU', 'Lomé', 'Lomé A'],
        ['Dogbéavou', 'Lomé', 'Lomé A'],
        ['Foyer des Jeunes Filles', 'Lomé', 'Lomé A'],
        ['Gakli', 'Lomé', 'Lomé A'],
        ['Garage Central', 'Lomé', 'Lomé A'],
        ['Gendarmerie', 'Maritime', 'Fortia'],
        ['Hilakondji', 'Lomé', 'Anfoin'],
        ['Kagnikopé', 'Lomé', 'Lomé B'],
        ['Kagome', 'Lomé', 'Lomé B'],
        ['Kovié', 'Lomé', 'Lomé A'],
        ['Kpogan', 'Lomé', 'Lomé B'],
        ['LCT 1', 'Lomé', 'Lomé B'],
        ['LCT 2', 'Lomé', 'Lomé B'],
        ['Légnassito', 'Lomé', 'Lomé C'],
        ['Lomé AB', 'Lomé', 'Lomé B'],
        ['Manumétal', 'Maritime', 'Lomé D'],
        ['Moyenne Entreprise', 'Lomé', 'Lomé B'],
        ["N'danida", 'Lomé', 'Lomé D'],
        ['PIA 1', 'Maritime', 'Lomé D'],
        ['PIA 2', 'Maritime', 'Lomé D'],
        ['Raffinerie', 'Lomé', 'Lomé B'],
        ['Saint Kizito', 'Lomé', 'Lomé A'],
        ['Sogbossito', 'Lomé', 'Lomé C'],
        ['Sototoes', 'Lomé', 'Lomé B'],
        ['Tabligbo', 'Maritime', 'Gendarmerie'],
        ['Terminal Clinker', 'Lomé', 'Lomé B'],
        ['Togo Terminal', 'Lomé', 'Lomé B'],
        ['Toyota', 'Lomé', 'Lomé A'],
        ['Tsévié', 'Maritime', 'Lomé D'],
        ['Vogan', 'Maritime', 'Anfoin'],
        ['DIR.TP.Public', 'Maritime', 'Lomé Siège'],
        ['SGGG', 'Maritime', 'Lomé Siège'],
        ['Togblé', 'Lomé', 'Lomé A'],
        ['Akodessawa 2', 'Lomé', 'Lomé A'],
        ['Adamavo', 'Lomé', 'Lomé B'],
        ['Adewi', 'Lomé', 'Lomé A'],
        ['Adidogomé', 'Lomé', 'Lomé A'],
        ['Afagnan', 'Maritime', 'Fortia'],
        ['Africa Plastic', 'Maritime', 'Lomé D'],
        ['Agbelouvé', 'Maritime', 'Lomé D'],
        ['Agoènyivé', 'Lomé', 'Lomé A'],
        ['Ahepé', 'Maritime', 'Fortia'],
        ['Aneho', 'Maritime', 'Anfoin'],
        ['Arrivée T1-1', 'Lomé', 'Lomé A'],
        ['Arrivée T1-2', 'Lomé', 'Lomé A'],
        ['Arrivée T2', 'Lomé', 'Lomé A'],
        ['Arrivée T3', 'Lomé', 'Lomé A'],
        ['Arrivée T5', 'Lomé', 'Lomé B'],
        ['Arrivée T7', 'Lomé', 'Lomé B'],
        ['Arrivée TR 1', 'Lomé', 'Lomé C'],
        ['Arrivée TR 2', 'Lomé', 'Lomé C'],
        ['Assemblée de Dieu', 'Lomé', 'Lomé A'],
        ['Avenou', 'Lomé', 'Lomé A'],
        ['Baguida', 'Lomé', 'Lomé B'],
        ['Cable Direct', 'Lomé', 'Lomé B'],
        ['Camp GP', 'Lomé', 'Lomé C'],
        ['Casablanca', 'Lomé', 'Lomé A'],
        ['CEET1', 'Lomé', 'Lomé A'],
        ['CEET2', 'Lomé', 'Lomé A'],
        ['Centre', 'Lomé', 'Lomé B'],
        ['Cimtogo', 'Lomé', 'Lomé B'],
        ['Coopérative', 'Lomé', 'Lomé Siège'],
        ['Deux Février', 'Lomé', 'Lomé A'],
    ];

    public function run(): void
    {
        $count = 0;

        foreach ($this->rows as [$nom, $zone, $poste]) {
            $code = Str::upper(Str::slug($nom, '_'));

            Departement::updateOrCreate(
                ['code' => $code],
                [
                    'nom'                   => $nom,
                    'zone'                  => $zone,
                    'direction_exploitation'=> $zone,
                    'poste_repartition'     => $poste,
                    'poste_source'          => $poste,
                    'is_active'             => true,
                ]
            );
            $count++;
        }

        $this->command?->info("✅ $count départs (catalogue) importés.");
    }
}
