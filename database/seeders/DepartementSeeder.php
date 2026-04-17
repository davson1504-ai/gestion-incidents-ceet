<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartementSeeder extends Seeder
{
    private array $rows = [
        ['nom' => 'Direction EAMAU', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Dogbéavou', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Foyer des Jeunes Filles', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Gakli', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Garage Central', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Gendarmerie', 'zone' => 'Maritime', 'poste_repartition' => 'Fortia'],
        ['nom' => 'Hilakondji', 'zone' => 'Lomé', 'poste_repartition' => 'Anfoin'],
        ['nom' => 'Kagnikopé', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Kagome', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Kovié', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Kpogan', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'LCT 1', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'LCT 2', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Légnassito', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Lomé AB', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Manumétal', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé D'],
        ['nom' => 'Moyenne Entreprise', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => "N'danida", 'zone' => 'Lomé', 'poste_repartition' => 'Lomé D'],
        ['nom' => 'PIA 1', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé D'],
        ['nom' => 'PIA 2', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé D'],
        ['nom' => 'Raffinerie', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Saint Kizito', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Sogbossito', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Sototoes', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Tabligbo', 'zone' => 'Maritime', 'poste_repartition' => 'Gendarmerie'],
        ['nom' => 'Terminal Clinker', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Togo Terminal', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Toyota', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Tsévié', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé D'],
        ['nom' => 'Vogan', 'zone' => 'Maritime', 'poste_repartition' => 'Anfoin'],
        ['nom' => 'DIR.TP.Public', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé Siège'],
        ['nom' => 'SGGG', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé Siège'],
        ['nom' => 'Togblé', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Akodessawa 2', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Adamavo', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Adewi', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Adidogomé', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Afagnan', 'zone' => 'Maritime', 'poste_repartition' => 'Fortia'],
        ['nom' => 'Africa Plastic', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé D'],
        ['nom' => 'Agbelouvé', 'zone' => 'Maritime', 'poste_repartition' => 'Lomé D'],
        ['nom' => 'Agoènyivé', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Ahepé', 'zone' => 'Maritime', 'poste_repartition' => 'Fortia'],
        ['nom' => 'Aneho', 'zone' => 'Maritime', 'poste_repartition' => 'Anfoin'],
        ['nom' => 'Arrivée T1-1', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Arrivée T1-2', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Arrivée T2', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Arrivée T3', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Arrivée T5', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Arrivée T7', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Arrivée TR 1', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Arrivée TR 2', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Assemblée de Dieu', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Avenou', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Baguida', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Cable Direct', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Camp GP', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Casablanca', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'CEET1', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'CEET2', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Centre', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Cimtogo', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé B'],
        ['nom' => 'Coopérative', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé Siège'],
        ['nom' => 'Deux Février', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé A'],
        ['nom' => 'Adakpame Nord', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Bè Kpota', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Attikoume', 'zone' => 'Lomé', 'poste_repartition' => 'Lomé C'],
        ['nom' => 'Kehou', 'zone' => 'Maritime', 'poste_repartition' => 'Fortia'],
        ['nom' => 'Kpalime Centre', 'zone' => 'Plateaux', 'poste_repartition' => 'Kpalimé'],
        ['nom' => 'Atakpame Centre', 'zone' => 'Plateaux', 'poste_repartition' => 'Atakpamé'],
        ['nom' => 'Sokode Centre', 'zone' => 'Centrale', 'poste_repartition' => 'Sokodé'],
        ['nom' => 'Kara Ville', 'zone' => 'Kara', 'poste_repartition' => 'Kara'],
        ['nom' => 'Dapaong Centre', 'zone' => 'Savanes', 'poste_repartition' => 'Dapaong'],
        ['nom' => 'Mango Ville', 'zone' => 'Savanes', 'poste_repartition' => 'Mango'],
        ['nom' => 'Cinkasse', 'zone' => 'Savanes', 'poste_repartition' => 'Dapaong'],
    ];

    private array $arriveeCharges = [
        'Arrivée T1-1' => 760,
        'Arrivée T1-2' => 720,
        'Arrivée T2' => 680,
        'Arrivée T3' => 610,
        'Arrivée T5' => 640,
        'Arrivée T7' => 620,
        'Arrivée TR 1' => 540,
        'Arrivée TR 2' => 520,
    ];

    private array $industrialCharges = [
        'Cimtogo' => 560,
        'Raffinerie' => 430,
        'PIA 1' => 520,
        'PIA 2' => 500,
        'Africa Plastic' => 360,
        'Manumétal' => 470,
        'Terminal Clinker' => 540,
        'Togo Terminal' => 390,
    ];

    private array $urbanCharges = [
        'Lomé AB' => 285,
        'Centre' => 260,
        'Baguida' => 225,
        'Adidogomé' => 235,
        'Kagnikopé' => 210,
        'Agoènyivé' => 245,
    ];

    private array $regionalCharges = [
        'Kpalime Centre' => 180,
        'Atakpame Centre' => 165,
        'Sokode Centre' => 175,
        'Kara Ville' => 185,
        'Dapaong Centre' => 160,
        'Mango Ville' => 140,
    ];

    private array $transformers = [
        'CEET1' => 'T1',
        'CEET2' => 'T2',
        'LCT 1' => 'T5',
        'LCT 2' => 'T7',
        'Arrivée T1-1' => 'T1',
        'Arrivée T1-2' => 'T1',
        'Arrivée T2' => 'T2',
        'Arrivée T3' => 'T3',
        'Arrivée T5' => 'T5',
        'Arrivée T7' => 'T7',
        'Arrivée TR 1' => 'TR 1',
        'Arrivée TR 2' => 'TR 2',
        'Cimtogo' => 'T5',
        'Raffinerie' => 'T2',
        'PIA 1' => 'T7',
        'PIA 2' => 'T7',
        'Africa Plastic' => 'T7',
        'Manumétal' => 'T5',
        'Terminal Clinker' => 'T5',
        'Togo Terminal' => 'T5',
    ];

    private array $arrivees = [
        'CEET1' => 'Arrivée T1-1',
        'CEET2' => 'Arrivée T2',
        'LCT 1' => 'Arrivée T5',
        'LCT 2' => 'Arrivée T7',
        'Cimtogo' => 'Arrivée T5',
        'Raffinerie' => 'Arrivée T2',
        'PIA 1' => 'Arrivée T7',
        'PIA 2' => 'Arrivée T7',
        'Africa Plastic' => 'Arrivée T7',
        'Manumétal' => 'Arrivée T5',
        'Terminal Clinker' => 'Arrivée T5',
        'Togo Terminal' => 'Arrivée T5',
    ];

    public function run(): void
    {
        $count = 0;

        foreach ($this->rows as $row) {
            $code = Str::upper(Str::slug($row['nom'], '_'));
            $transformateur = $this->transformers[$row['nom']] ?? null;

            Departement::updateOrCreate(
                ['code' => $code],
                [
                    'nom' => $row['nom'],
                    'zone' => $row['zone'],
                    'direction_exploitation' => $row['zone'],
                    'poste_repartition' => $row['poste_repartition'],
                    'poste_source' => $row['poste_repartition'],
                    'transformateur' => $transformateur,
                    'arrivee' => $this->resolveArrivee($row['nom'], $transformateur),
                    'charge_maximale' => $this->resolveCharge($row['nom']),
                    'charge_unite' => 'A',
                    'is_active' => true,
                ]
            );

            $count++;
        }

        $this->command?->info("✅ {$count} départs CEET enrichis avec charges et transformateurs.");
    }

    private function resolveCharge(string $nom): int
    {
        if (isset($this->arriveeCharges[$nom])) {
            return $this->arriveeCharges[$nom];
        }

        if (isset($this->industrialCharges[$nom])) {
            return $this->industrialCharges[$nom];
        }

        if (isset($this->urbanCharges[$nom])) {
            return $this->urbanCharges[$nom];
        }

        if (isset($this->regionalCharges[$nom])) {
            return $this->regionalCharges[$nom];
        }

        return 50 + (abs(crc32($nom)) % 101);
    }

    private function resolveArrivee(string $nom, ?string $transformateur): ?string
    {
        if (isset($this->arrivees[$nom])) {
            return $this->arrivees[$nom];
        }

        if (Str::startsWith($nom, 'Arrivée')) {
            return $nom;
        }

        return $transformateur ? 'Arrivée '.$transformateur : null;
    }
}
