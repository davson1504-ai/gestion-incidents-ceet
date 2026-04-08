<?php

namespace Database\Seeders;

use App\Models\Cause;
use App\Models\Departement;
use App\Models\Incident;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\TypeIncident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $departementIds = Departement::query()->pluck('id')->all();
        $typeIds = TypeIncident::query()->pluck('id')->all();
        $causesByType = Cause::query()->get(['id', 'type_incident_id'])->groupBy('type_incident_id');
        $statuts = Statut::query()->get(['id', 'is_final']);
        $prioriteIds = Priorite::query()->pluck('id')->all();
        $userIds = User::query()->pluck('id')->all();

        if (
            empty($departementIds)
            || empty($typeIds)
            || $statuts->isEmpty()
            || empty($prioriteIds)
            || empty($userIds)
        ) {
            $this->command?->warn('IncidentSeeder ignore: catalogues/utilisateurs incomplets.');

            return;
        }

        $titles = [
            'Coupure sur feeder principal',
            'Surtension sur poste source',
            'Defaut d isolement',
            'Panne transformateur distribution',
            'Perte de communication SCADA',
            'Declenchement intempestif disjoncteur',
            'Incident sur ligne moyenne tension',
            'Interruption secteur urbain',
        ];

        $locations = [
            'Lome - Tokoin',
            'Lome - Agoe',
            'Aniego',
            'Kara - Zone centrale',
            'Atakpame - Poste 2',
            'Sokode - Ligne Ouest',
            'Kpalime - Transformateur T3',
            'Tsievié - Depart Nord',
        ];

        $actions = [
            'Diagnostic initial et securisation de zone',
            'Isolement du depart impacte',
            'Reconfiguration reseau temporaire',
            'Intervention equipe maintenance',
            'Verification et remise sous tension progressive',
        ];

        $resolutions = [
            'Incident resolu apres remplacement composant defaillant.',
            'Alimentation retablie apres manoeuvre et controle final.',
            'Anomalie corrigee et service normal confirme.',
            'Cause maitrisee, suivi preventif planifie.',
        ];

        $baseNow = now();

        for ($i = 1; $i <= 40; $i++) {
            $typeId = Arr::random($typeIds);
            $causePool = $causesByType->get($typeId);
            $causeId = $causePool && $causePool->isNotEmpty()
                ? $causePool->random()->id
                : null;

            $statut = $statuts->random();
            $start = (clone $baseNow)
                ->subDays(random_int(0, 45))
                ->subMinutes(random_int(20, 720));

            $end = null;
            $duration = null;
            $cloturedAt = null;
            $resolution = null;

            if ($statut->is_final) {
                $end = (clone $start)->addMinutes(random_int(30, 360));
                $duration = $start->diffInMinutes($end);
                $cloturedAt = $end;
                $resolution = Arr::random($resolutions);
            }

            Incident::updateOrCreate(
                ['code_incident' => sprintf('INC-DEMO-%05d', $i)],
                [
                    'titre' => Arr::random($titles),
                    'description' => 'Incident de demonstration genere automatiquement pour les tests de suivi.',
                    'departement_id' => Arr::random($departementIds),
                    'type_incident_id' => $typeId,
                    'cause_id' => $causeId,
                    'status_id' => $statut->id,
                    'priorite_id' => Arr::random($prioriteIds),
                    'localisation' => Arr::random($locations),
                    'date_debut' => $start,
                    'date_fin' => $end,
                    'duree_minutes' => $duration,
                    'operateur_id' => Arr::random($userIds),
                    'responsable_id' => random_int(0, 100) < 85 ? Arr::random($userIds) : null,
                    'superviseur_id' => random_int(0, 100) < 65 ? Arr::random($userIds) : null,
                    'actions_menees' => Arr::random($actions),
                    'resolution_summary' => $resolution,
                    'clotured_at' => $cloturedAt,
                ]
            );
        }

        $this->command?->info('40 incidents de demonstration ont ete crees/mis a jour.');
    }
}

