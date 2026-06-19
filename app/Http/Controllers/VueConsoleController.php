<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class VueConsoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'permission:incidents.view']);
    }

    public function __invoke(Request $request): View
    {
        $currentUser = $request->user();
        $openBaseQuery = $this->openIncidentBaseQuery($currentUser);

        $liveIncidents = (clone $openBaseQuery)
            ->with(['departement', 'typeIncident', 'priorite', 'status', 'responsable'])
            ->leftJoin('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->orderByRaw('CASE WHEN priorites.niveau IS NULL THEN 999 ELSE priorites.niveau END ASC')
            ->orderBy('incidents.date_debut')
            ->limit(8)
            ->get();

        $activeFaults = (clone $openBaseQuery)->count();

        $criticalFaults = (clone $openBaseQuery)
            ->join('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->where('priorites.niveau', 1)
            ->count();

        $recentCriticalAlerts = (clone $openBaseQuery)
            ->with(['departement', 'typeIncident', 'priorite'])
            ->join('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->where('priorites.niveau', '<=', 2)
            ->orderBy('priorites.niveau')
            ->orderBy('incidents.date_debut')
            ->limit(2)
            ->get();

        $oldestActiveIncident = (clone $openBaseQuery)
            ->with(['departement', 'typeIncident', 'priorite', 'status'])
            ->orderBy('incidents.date_debut')
            ->first();

        $networkNodes = Departement::query()
            ->withCount([
                'incidents as open_incidents_count' => function (Builder $query): void {
                    $query->join('statuses', 'statuses.id', '=', 'incidents.status_id')
                        ->where('statuses.is_final', false);
                },
                'incidents as critical_incidents_count' => function (Builder $query): void {
                    $query->join('statuses', 'statuses.id', '=', 'incidents.status_id')
                        ->join('priorites', 'priorites.id', '=', 'incidents.priorite_id')
                        ->where('statuses.is_final', false)
                        ->where('priorites.niveau', 1);
                },
            ])
            ->orderByDesc('critical_incidents_count')
            ->orderByDesc('open_incidents_count')
            ->orderBy('nom')
            ->limit(6)
            ->get();

        $totalCapacity = (float) Departement::query()
            ->where('is_active', true)
            ->whereNotNull('charge_maximale')
            ->sum('charge_maximale');

        $impactedCapacity = (float) Departement::query()
            ->where('is_active', true)
            ->whereNotNull('charge_maximale')
            ->whereHas('incidents', function (Builder $query): void {
                $query->join('statuses', 'statuses.id', '=', 'incidents.status_id')
                    ->where('statuses.is_final', false);
            })
            ->sum('charge_maximale');

        $networkLoad = $totalCapacity > 0
            ? round(min(100, ($impactedCapacity / $totalCapacity) * 100), 1)
            : round(min(100, $activeFaults * 6.5), 1);

        $averageResponseMinutes = $this->averageResponseMinutes(now()->subDays(30), now());
        $previousAverageResponseMinutes = $this->averageResponseMinutes(now()->subDays(60), now()->subDays(30));

        $responseDeltaMinutes = $averageResponseMinutes !== null && $previousAverageResponseMinutes !== null
            ? round($averageResponseMinutes - $previousAverageResponseMinutes, 1)
            : null;

        return view('incidents.vue-console', [
            'liveIncidents' => $liveIncidents,
            'activeFaults' => $activeFaults,
            'criticalFaults' => $criticalFaults,
            'recentCriticalAlerts' => $recentCriticalAlerts,
            'oldestActiveIncident' => $oldestActiveIncident,
            'networkNodes' => $networkNodes,
            'networkLoad' => $networkLoad,
            'nominalFrequency' => $criticalFaults > 0 ? 49.98 : 50.00,
            'frequencyStatus' => $criticalFaults > 0 ? 'Sous surveillance' : 'Plage stable',
            'averageResponseMinutes' => $averageResponseMinutes,
            'responseDeltaMinutes' => $responseDeltaMinutes,
            'lastCheckAt' => now()->format('H:i:s'),
        ]);
    }

    private function openIncidentBaseQuery(?User $user): Builder
    {
        return Incident::query()
            ->when($user, fn (Builder $query) => $query->visibleToUser($user))
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->where('statuses.is_final', false)
            ->select('incidents.*');
    }

    private function averageResponseMinutes(?Carbon $from = null, ?Carbon $to = null): ?float
    {
        $incidents = Incident::query()
            ->with(['interventions' => fn ($query) => $query->orderBy('started_at')])
            ->whereHas('interventions')
            ->when($from, fn (Builder $query) => $query->where('date_debut', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('date_debut', '<', $to))
            ->latest('date_debut')
            ->limit(100)
            ->get();

        $minutes = $incidents
            ->map(function (Incident $incident): ?int {
                $firstStartedAt = $incident->interventions->first()?->started_at;

                if (! $incident->date_debut || ! $firstStartedAt) {
                    return null;
                }

                return $incident->date_debut->diffInMinutes($firstStartedAt);
            })
            ->filter(fn (?int $value): bool => $value !== null);

        if ($minutes->isEmpty()) {
            return null;
        }

        return round((float) $minutes->avg(), 1);
    }
}