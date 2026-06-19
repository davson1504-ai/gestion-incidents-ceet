<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentAction;
use App\Models\User;
use App\Support\RoleAliases;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(private readonly ReportPageService $reportPageService) {}

    /** @param array{date_from?: string|null, date_to?: string|null} $filters */
    public function buildForUser(User $user, array $filters): array
    {
        $viewData = $this->buildBaseViewData($filters);

        if ($user->isOperateur()) {
            return [
                'view' => 'dashboard-operator',
                'data' => array_merge($viewData, $this->operatorData($user)),
            ];
        }

        if ($user->isSuperviseur()) {
            return [
                'view' => 'dashboard-supervisor',
                'data' => array_merge($viewData, $this->supervisorData($user)),
            ];
        }

        return [
            'view' => 'dashboard',
            'data' => $viewData,
        ];
    }

    /** @param array{date_from?: string|null, date_to?: string|null} $filters */
    private function buildBaseViewData(array $filters): array
    {
        $cacheKey = 'dashboard.data.' . md5(json_encode($filters));
        $today = now()->toDateString();
        $now = now();

        return Cache::remember($cacheKey, 300, function () use ($filters, $today, $now) {
            $baseQuery = $this->filteredIncidents($filters);

            $summary = (clone $baseQuery)
                ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
                ->selectRaw(
                    '
                    COUNT(*) as total_count,
                    SUM(CASE WHEN statuses.is_final = 0 OR statuses.is_final IS NULL THEN 1 ELSE 0 END) as open_count,
                    SUM(CASE WHEN statuses.is_final = 1 THEN 1 ELSE 0 END) as closed_count,
                    AVG(incidents.duree_minutes) as avg_duration,
                    SUM(CASE WHEN DATE(incidents.date_fin) = ? AND statuses.is_final = 1 THEN 1 ELSE 0 END) as today_resolved
                    ',
                    [$today]
                )
                ->first();

            $total = (int) ($summary?->total_count ?? 0);

            if ($total === 0) {
                return $this->emptyDashboardData($filters);
            }

            $openCount = (int) ($summary->open_count ?? 0);
            $closedCount = (int) ($summary->closed_count ?? 0);
            $avgDuration = $summary->avg_duration !== null ? (float) $summary->avg_duration : null;
            $todayResolved = (int) ($summary->today_resolved ?? 0);

            $availabilityRate = $total > 0
                ? round((($total - $openCount) / $total) * 100, 1)
                : 100.0;

            $byStatus = collect((clone $baseQuery)
                ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
                ->selectRaw('statuses.id, statuses.libelle, statuses.couleur, COUNT(*) as total')
                ->groupBy('statuses.id', 'statuses.libelle', 'statuses.couleur')
                ->orderByDesc('total')
                ->get())
                ->map(fn ($row) => [
                    'label' => $row->libelle ?? 'N/A',
                    'color' => $row->couleur ?? '#6c757d',
                    'total' => (int) $row->total,
                ])
                ->values();

            $byPriorite = collect((clone $baseQuery)
                ->leftJoin('priorites', 'priorites.id', '=', 'incidents.priorite_id')
                ->selectRaw('priorites.id, priorites.libelle, priorites.couleur, COUNT(*) as total')
                ->groupBy('priorites.id', 'priorites.libelle', 'priorites.couleur')
                ->orderByDesc('total')
                ->get())
                ->map(fn ($row) => [
                    'label' => $row->libelle ?? 'N/A',
                    'color' => $row->couleur ?? '#e9ecef',
                    'total' => (int) $row->total,
                ])
                ->values();

            $byType = collect((clone $baseQuery)
                ->leftJoin('type_incidents', 'type_incidents.id', '=', 'incidents.type_incident_id')
                ->selectRaw('type_incidents.id, type_incidents.libelle, COUNT(*) as total')
                ->groupBy('type_incidents.id', 'type_incidents.libelle')
                ->orderByDesc('total')
                ->get())
                ->map(fn ($row) => [
                    'label' => $row->libelle ?? 'N/A',
                    'total' => (int) $row->total,
                ])
                ->values();

            $byCause = collect((clone $baseQuery)
                ->leftJoin('causes', 'causes.id', '=', 'incidents.cause_id')
                ->selectRaw('causes.id, causes.libelle, COUNT(*) as total')
                ->groupBy('causes.id', 'causes.libelle')
                ->orderByDesc('total')
                ->limit(10)
                ->get())
                ->map(fn ($row) => [
                    'label' => $row->libelle ?? 'N/A',
                    'total' => (int) $row->total,
                ])
                ->values();

            $topDepart = collect((clone $baseQuery)
                ->leftJoin('departements', 'departements.id', '=', 'incidents.departement_id')
                ->selectRaw('departements.id, departements.nom, COUNT(*) as total')
                ->groupBy('departements.id', 'departements.nom')
                ->orderByDesc('total')
                ->limit(7)
                ->get())
                ->map(fn ($row) => [
                    'label' => $row->nom ?? 'N/A',
                    'total' => (int) $row->total,
                ])
                ->values();

            $timeseries = collect((clone $baseQuery)
                ->selectRaw('DATE(incidents.date_debut) as d, COUNT(*) as total')
                ->groupBy('d')
                ->orderBy('d')
                ->get())
                ->map(fn ($row) => [
                    'd' => $row->d,
                    'total' => (int) $row->total,
                ])
                ->values();

            $recentIncidents = (clone $baseQuery)
                ->with(['status', 'priorite', 'departement', 'typeIncident', 'cause'])
                ->orderByDesc('incidents.date_debut')
                ->limit(5)
                ->get();

            $adminUsers = User::query()
                ->with(['roles', 'departement'])
                ->latest('updated_at')
                ->limit(5)
                ->get();

            $totalUsers = User::query()->count();

            $roleCountsRow = DB::query()
                ->selectRaw('1 as anchor')
                ->selectSub($this->roleCountSubquery(RoleAliases::adminNames(), RoleAliases::adminLikePatterns()), 'admin_count')
                ->selectSub($this->roleCountSubquery(RoleAliases::supervisorNames(), RoleAliases::supervisorLikePatterns()), 'supervisor_count')
                ->selectSub($this->roleCountSubquery(RoleAliases::operatorNames(), RoleAliases::operatorLikePatterns()), 'operator_count')
                ->first();

            $roleCounts = [
                ['label' => 'Administrateur', 'count' => (int) ($roleCountsRow->admin_count ?? 0)],
                ['label' => 'Superviseur', 'count' => (int) ($roleCountsRow->supervisor_count ?? 0)],
                ['label' => 'Operateur Terrain', 'count' => (int) ($roleCountsRow->operator_count ?? 0)],
            ];

            $currentWeekAvg = (float) ((clone $baseQuery)
                ->whereBetween('incidents.date_debut', [$now->copy()->subDays(7), $now])
                ->whereNotNull('incidents.duree_minutes')
                ->avg('incidents.duree_minutes') ?? 0);

            $previousWeekAvg = (float) ((clone $baseQuery)
                ->whereBetween('incidents.date_debut', [$now->copy()->subDays(14), $now->copy()->subDays(7)])
                ->whereNotNull('incidents.duree_minutes')
                ->avg('incidents.duree_minutes') ?? 0);

            $weekDelta = $previousWeekAvg > 0
                ? round((($previousWeekAvg - $currentWeekAvg) / $previousWeekAvg) * 100, 1)
                : null;

            $focusZones = collect($topDepart)
                ->take(2)
                ->pluck('label')
                ->filter(fn (string $label) => $label !== 'N/A')
                ->values();

            $focusText = $focusZones->isNotEmpty()
                ? $focusZones->implode(' et ')
                : 'les zones critiques';

            return [
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
                'adminUsers' => $adminUsers,
                'totalUsers' => $totalUsers,
                'roleCounts' => $roleCounts,
                'weekDelta' => $weekDelta,
                'focusText' => $focusText,
                'lastCheckAt' => $now->format('H:i:s'),
            ];
        });
    }

    private function emptyDashboardData(array $filters): array
    {
        return [
            'filters' => $filters,
            'kpis' => ['total' => 0, 'openCount' => 0, 'closedCount' => 0, 'avgDuration' => null],
            'todayResolved' => 0,
            'availabilityRate' => 100.0,
            'byStatus' => [],
            'byPriorite' => [],
            'byType' => [],
            'byCause' => [],
            'topDepart' => [],
            'timeseries' => [],
            'recentIncidents' => [],
            'adminUsers' => collect(),
            'totalUsers' => 0,
            'roleCounts' => [
                ['label' => 'Administrateur', 'count' => 0],
                ['label' => 'Superviseur', 'count' => 0],
                ['label' => 'Operateur Terrain', 'count' => 0],
            ],
            'weekDelta' => null,
            'focusText' => 'les zones critiques',
            'lastCheckAt' => now()->format('H:i:s'),
        ];
    }

    private function operatorData(User $user): array
    {
        $myOpenIncidents = Incident::query()
            ->with(['departement', 'typeIncident', 'priorite', 'status'])
            ->where('responsable_id', $user->id)
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', false)
            ->select('incidents.*')
            ->leftJoin('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->orderByRaw('CASE WHEN priorites.niveau IS NULL THEN 999 ELSE priorites.niveau END ASC')
            ->orderBy('incidents.date_debut')
            ->limit(10)
            ->get()
            ->map(function ($incident) {
                $incident->duree_en_attente = $incident->date_debut
                    ? $incident->date_debut->diffInMinutes(now())
                    : null;

                return $incident;
            });

        $myTotalOpen = Incident::query()
            ->where('responsable_id', $user->id)
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', false)
            ->count();

        $myResolvedToday = Incident::query()
            ->where('responsable_id', $user->id)
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', true)
            ->whereDate('incidents.date_fin', now()->toDateString())
            ->count();

        $myTotalMonth = Incident::query()
            ->where('responsable_id', $user->id)
            ->whereMonth('date_debut', now()->month)
            ->whereYear('date_debut', now()->year)
            ->count();

        $myRecentActions = IncidentAction::query()
            ->with(['incident'])
            ->where('user_id', $user->id)
            ->latest('action_date')
            ->limit(5)
            ->get();

        return compact(
            'myOpenIncidents',
            'myTotalOpen',
            'myResolvedToday',
            'myTotalMonth',
            'myRecentActions'
        );
    }

    private function supervisorData(User $user): array
    {
        $reportDashboard = $this->reportPageService->buildMonthlyIndexData(now()->startOfMonth(), [
            'period' => now()->format('Y-m'),
            'departement_id' => null,
            'cause_id' => null,
        ]);

        $teamOpenIncidents = Incident::query()
            ->with(['departement', 'typeIncident', 'priorite', 'status', 'operateur'])
            ->where(function ($query) use ($user) {
                $query->where('superviseur_id', $user->id)
                    ->orWhere('responsable_id', $user->id);
            })
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', false)
            ->select('incidents.*')
            ->leftJoin('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->orderByRaw('CASE WHEN priorites.niveau IS NULL THEN 999 ELSE priorites.niveau END ASC')
            ->orderBy('incidents.date_debut')
            ->limit(15)
            ->get()
            ->map(function ($incident) {
                $incident->duree_en_attente = $incident->date_debut
                    ? $incident->date_debut->diffInMinutes(now())
                    : null;

                return $incident;
            });

        $pendingValidation = Incident::query()
            ->with(['departement', 'priorite', 'status', 'operateur'])
            ->where('superviseur_id', $user->id)
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', false)
            ->join('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->where('priorites.niveau', 1)
            ->select('incidents.*')
            ->orderBy('incidents.date_debut')
            ->limit(5)
            ->get();

        $teamTotal = Incident::query()
            ->where('superviseur_id', $user->id)
            ->whereMonth('date_debut', now()->month)
            ->whereYear('date_debut', now()->year)
            ->count();

        $teamResolved = Incident::query()
            ->where('superviseur_id', $user->id)
            ->whereMonth('date_debut', now()->month)
            ->whereYear('date_debut', now()->year)
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', true)
            ->count();

        $teamResolutionRate = $teamTotal > 0
            ? round(($teamResolved / $teamTotal) * 100, 1)
            : 0.0;

        $teamOpenCount = Incident::query()
            ->where('superviseur_id', $user->id)
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', false)
            ->count();

        $teamOperators = User::query()
            ->with('departement')
            ->where('is_active', true)
            ->where(function (Builder $query) {
                foreach (RoleAliases::operatorLikePatterns() as $pattern) {
                    $query->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'like', $pattern));
                }
            })
            ->withCount([
                'incidentsDeclares as open_count' => function ($query) {
                    $query->join('statuses', 'statuses.id', '=', 'incidents.status_id')
                        ->where('statuses.is_final', false);
                },
            ])
            ->orderByDesc('open_count')
            ->limit(8)
            ->get();

        return compact(
            'teamOpenIncidents',
            'pendingValidation',
            'teamTotal',
            'teamResolved',
            'teamResolutionRate',
            'teamOpenCount',
            'teamOperators',
            'reportDashboard'
        );
    }

    /** @param array{date_from?: string|null, date_to?: string|null} $filters */
    private function filteredIncidents(array $filters): Builder
    {
        return Incident::query()
            ->when(
                $filters['date_from'] ?? null,
                fn (Builder $query, string $value) => $query->whereDate('incidents.date_debut', '>=', $value)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn (Builder $query, string $value) => $query->whereDate('incidents.date_debut', '<=', $value)
            );
    }

    /** @param array<int, string> $exactNames @param array<int, string> $likePatterns */
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
