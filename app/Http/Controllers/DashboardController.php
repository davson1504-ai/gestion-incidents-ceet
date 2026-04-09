<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function __invoke(Request $request): View
    {
        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $base = Incident::query()
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('date_debut', '<=', $v));

        $rows = (clone $base)
            ->with(['statut', 'priorite', 'typeIncident', 'departement', 'cause'])
            ->latest('date_debut')
            ->get();

        $total = $rows->count();
        $openCount = $rows->filter(fn ($i) => $i->statut && ! $i->statut->is_final)->count();
        $closedCount = $rows->filter(fn ($i) => $i->statut && $i->statut->is_final)->count();
        $avgDuration = $rows->whereNotNull('duree_minutes')->avg('duree_minutes');
        $todayResolved = $rows->filter(fn ($i) => $i->statut?->is_final && $i->date_fin?->isToday())->count();
        $availabilityRate = $total > 0 ? round((($total - $openCount) / $total) * 100, 1) : 100.0;

        $byStatus = $rows->groupBy('status_id')->map(function ($g) {
            $s = $g->first()->statut;

            return [
                'label' => $s?->libelle ?? 'N/A',
                'color' => $s?->couleur ?? '#6c757d',
                'total' => $g->count(),
            ];
        })->values();

        $byPriorite = $rows->groupBy('priorite_id')->map(function ($g) {
            $p = $g->first()->priorite;

            return [
                'label' => $p?->libelle ?? 'N/A',
                'color' => $p?->couleur ?? '#e9ecef',
                'total' => $g->count(),
            ];
        })->values();

        $byType = $rows->groupBy('type_incident_id')->map(function ($g) {
            $t = $g->first()->typeIncident;

            return [
                'label' => $t?->libelle ?? 'N/A',
                'total' => $g->count(),
            ];
        })->values();

        $byCause = $rows->groupBy('cause_id')->map(function ($g) {
            $c = $g->first()->cause;

            return [
                'label' => $c?->libelle ?? 'N/A',
                'total' => $g->count(),
            ];
        })->sortByDesc('total')->take(10)->values();

        $topDepart = $rows->groupBy('departement_id')->map(function ($g) {
            return [
                'label' => optional($g->first()->departement)->nom ?? 'N/A',
                'total' => $g->count(),
            ];
        })->sortByDesc('total')->take(7)->values();

        $timeseries = Incident::query()
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->when(! $filters['date_from'], fn ($q) => $q->whereDate('date_debut', '>=', now()->subDays(30)->toDateString()))
            ->selectRaw('DATE(date_debut) as d, count(*) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $recentIncidents = (clone $base)
            ->with(['departement', 'statut', 'cause'])
            ->latest('date_debut')
            ->limit(5)
            ->get();

        $countUsersByRole = static function (array $exactNames, array $likePatterns = []): int {
            return User::query()
                ->whereHas('roles', function ($q) use ($exactNames, $likePatterns) {
                    $q->where(function ($roleQuery) use ($exactNames, $likePatterns) {
                        if (count($exactNames) > 0) {
                            $roleQuery->whereIn('name', $exactNames);
                        }

                        foreach ($likePatterns as $pattern) {
                            $roleQuery->orWhere('name', 'like', $pattern);
                        }
                    });
                })
                ->count();
        };

        $adminCount = $countUsersByRole(['Administrateur', 'ADMINISTRATEUR'], ['Admin%']);
        $supervisorCount = $countUsersByRole(['Superviseur', 'SUPERVISEUR'], ['Super%']);
        $operatorCount = $countUsersByRole(['Operateur', 'Operateur Terrain'], ['Op%rateur%']);

        $roleCounts = [
            ['label' => 'Administrateur', 'count' => $adminCount],
            ['label' => 'Superviseur', 'count' => $supervisorCount],
            ['label' => 'Operateur Terrain', 'count' => $operatorCount],
        ];

        $currentWeekAvg = Incident::query()
            ->whereDate('date_debut', '>=', now()->subDays(7)->toDateString())
            ->whereNotNull('duree_minutes')
            ->avg('duree_minutes');
        $previousWeekAvg = Incident::query()
            ->whereDate('date_debut', '>=', now()->subDays(14)->toDateString())
            ->whereDate('date_debut', '<', now()->subDays(7)->toDateString())
            ->whereNotNull('duree_minutes')
            ->avg('duree_minutes');

        $weekDelta = null;
        if ($previousWeekAvg && $previousWeekAvg > 0) {
            $weekDelta = round((($previousWeekAvg - ($currentWeekAvg ?? 0)) / $previousWeekAvg) * 100, 1);
        }

        $focusZones = $topDepart->take(2)->pluck('label')->filter(fn ($l) => $l !== 'N/A')->values();
        $focusText = $focusZones->count() > 0
            ? $focusZones->implode(' et ')
            : 'les zones critiques';

        return view('dashboard', [
            'filters' => $filters,
            'kpis' => compact('total', 'openCount', 'closedCount', 'avgDuration'),
            'todayResolved' => $todayResolved,
            'availabilityRate' => $availabilityRate,
            'byStatus' => $byStatus,
            'byPriorite' => $byPriorite,
            'byType' => $byType,
            'byCause' => $byCause,
            'topDepart' => $topDepart,
            'timeseries' => $timeseries,
            'recentIncidents' => $recentIncidents,
            'roleCounts' => $roleCounts,
            'weekDelta' => $weekDelta,
            'focusText' => $focusText,
            'lastCheckAt' => now()->format('H:i:s'),
        ]);
    }
}

