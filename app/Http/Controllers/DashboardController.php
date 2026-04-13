<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// TODO: update views that still reference $incident->statut to use $incident->status.
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

        $aggregateRow = $this->filteredIncidents($filters)
            ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN statuses.is_final = 0 THEN 1 ELSE 0 END) as open_count')
            ->selectRaw('SUM(CASE WHEN statuses.is_final = 1 THEN 1 ELSE 0 END) as closed_count')
            ->selectRaw('AVG(incidents.duree_minutes) as avg_duration')
            ->first();

        $total = (int) ($aggregateRow->total ?? 0);
        $openCount = (int) ($aggregateRow->open_count ?? 0);
        $closedCount = (int) ($aggregateRow->closed_count ?? 0);
        $avgDuration = $aggregateRow->avg_duration !== null ? (float) $aggregateRow->avg_duration : null;

        $todayResolved = (int) $this->filteredIncidents($filters)
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', true)
            ->whereDate('incidents.date_fin', now()->toDateString())
            ->count();

        $availabilityRate = $total > 0
            ? round((($total - $openCount) / $total) * 100, 1)
            : 100.0;

        $byStatus = $this->filteredIncidents($filters)
            ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->selectRaw("COALESCE(statuses.libelle, 'N/A') as label")
            ->selectRaw("COALESCE(statuses.couleur, '#6c757d') as color")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('incidents.status_id', 'statuses.libelle', 'statuses.couleur')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'color' => $row->color,
                'total' => (int) $row->total,
            ])
            ->values();

        $byPriorite = $this->filteredIncidents($filters)
            ->leftJoin('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->selectRaw("COALESCE(priorites.libelle, 'N/A') as label")
            ->selectRaw("COALESCE(priorites.couleur, '#e9ecef') as color")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('incidents.priorite_id', 'priorites.libelle', 'priorites.couleur')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'color' => $row->color,
                'total' => (int) $row->total,
            ])
            ->values();

        $byType = $this->filteredIncidents($filters)
            ->leftJoin('type_incidents', 'type_incidents.id', '=', 'incidents.type_incident_id')
            ->selectRaw("COALESCE(type_incidents.libelle, 'N/A') as label")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('incidents.type_incident_id', 'type_incidents.libelle')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ])
            ->values();

        $byCause = $this->filteredIncidents($filters)
            ->leftJoin('causes', 'causes.id', '=', 'incidents.cause_id')
            ->selectRaw("COALESCE(causes.libelle, 'N/A') as label")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('incidents.cause_id', 'causes.libelle')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ])
            ->values();

        $topDepart = $this->filteredIncidents($filters)
            ->leftJoin('departements', 'departements.id', '=', 'incidents.departement_id')
            ->selectRaw("COALESCE(departements.nom, 'N/A') as label")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('incidents.departement_id', 'departements.nom')
            ->orderByDesc('total')
            ->limit(7)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ])
            ->values();

        $timeseries = $this->filteredIncidents($filters)
            ->when(! $filters['date_from'], fn (Builder $query) => $query->whereDate('incidents.date_debut', '>=', now()->subDays(30)->toDateString()))
            ->selectRaw('DATE(incidents.date_debut) as d, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(incidents.date_debut)'))
            ->orderBy('d')
            ->get();

        $recentIncidents = $this->filteredIncidents($filters)
            ->with(['departement', 'status', 'cause'])
            ->latest('incidents.date_debut')
            ->limit(5)
            ->get();

        $roleCountsRow = DB::query()
            ->selectRaw('1 as anchor')
            ->selectSub($this->roleCountSubquery(['Administrateur', 'ADMINISTRATEUR'], ['Admin%']), 'admin_count')
            ->selectSub($this->roleCountSubquery(['Superviseur', 'SUPERVISEUR'], ['Super%']), 'supervisor_count')
            ->selectSub($this->roleCountSubquery(['Opérateur', 'Operateur', 'Opérateur Terrain', 'Operateur Terrain'], ['Op%rateur%']), 'operator_count')
            ->first();

        $roleCounts = [
            ['label' => 'Administrateur', 'count' => (int) ($roleCountsRow->admin_count ?? 0)],
            ['label' => 'Superviseur', 'count' => (int) ($roleCountsRow->supervisor_count ?? 0)],
            ['label' => 'Operateur Terrain', 'count' => (int) ($roleCountsRow->operator_count ?? 0)],
        ];

        $currentWeekAvg = (float) ($this->filteredIncidents($filters)
            ->whereDate('incidents.date_debut', '>=', now()->subDays(7)->toDateString())
            ->whereNotNull('incidents.duree_minutes')
            ->avg('incidents.duree_minutes') ?? 0);

        $previousWeekAvg = (float) ($this->filteredIncidents($filters)
            ->whereDate('incidents.date_debut', '>=', now()->subDays(14)->toDateString())
            ->whereDate('incidents.date_debut', '<', now()->subDays(7)->toDateString())
            ->whereNotNull('incidents.duree_minutes')
            ->avg('incidents.duree_minutes') ?? 0);

        $weekDelta = null;
        if ($previousWeekAvg > 0) {
            $weekDelta = round((($previousWeekAvg - $currentWeekAvg) / $previousWeekAvg) * 100, 1);
        }

        $focusZones = collect($topDepart)
            ->take(2)
            ->pluck('label')
            ->filter(fn (string $label) => $label !== 'N/A')
            ->values();

        $focusText = $focusZones->isNotEmpty()
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

    private function filteredIncidents(array $filters): Builder
    {
        return Incident::query()
            ->when($filters['date_from'], fn (Builder $query, string $value) => $query->whereDate('incidents.date_debut', '>=', $value))
            ->when($filters['date_to'], fn (Builder $query, string $value) => $query->whereDate('incidents.date_debut', '<=', $value));
    }

    private function roleCountSubquery(array $exactNames, array $likePatterns = [])
    {
        return DB::table('model_has_roles as mhr')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->selectRaw('COUNT(DISTINCT mhr.model_id)')
            ->where('mhr.model_type', User::class)
            ->where(function ($query) use ($exactNames, $likePatterns) {
                if ($exactNames !== []) {
                    $query->whereIn('r.name', $exactNames);
                }

                foreach ($likePatterns as $pattern) {
                    $query->orWhere('r.name', 'like', $pattern);
                }
            });
    }
}