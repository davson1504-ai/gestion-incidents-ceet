<?php

namespace App\Services;

use App\Models\Cause;
use App\Models\Departement;
use Illuminate\Support\Carbon;

class ReportPageService
{
    public function __construct(private readonly ReportService $reportService) {}

    /** @param array{period: string, departement_id: mixed, cause_id: mixed} $filters */
    public function buildMonthlyIndexData(Carbon $period, array $filters): array
    {
        $currentFilters = [
            'date_from' => $period->copy()->startOfMonth()->toDateString(),
            'date_to' => $period->copy()->endOfMonth()->toDateString(),
            'departement_id' => $filters['departement_id'],
            'cause_id' => $filters['cause_id'],
        ];

        $previousPeriod = $period->copy()->subMonth();
        $previousFilters = [
            'date_from' => $previousPeriod->copy()->startOfMonth()->toDateString(),
            'date_to' => $previousPeriod->copy()->endOfMonth()->toDateString(),
            'departement_id' => $filters['departement_id'],
            'cause_id' => $filters['cause_id'],
        ];

        $currentSummary = $this->reportService->overview($currentFilters);
        $previousSummary = $this->reportService->overview($previousFilters);
        $departStats = collect($this->reportService->byDepartement($currentFilters));
        $previousDepartStats = collect($this->reportService->byDepartement($previousFilters))->keyBy('id');
        $dailyRows = collect($this->reportService->daily($currentFilters))->keyBy('jour');
        $byType = collect($this->reportService->byType($currentFilters))
            ->sortByDesc('total')
            ->take(4)
            ->values();
        $causeRows = collect($this->reportService->byCause($currentFilters))
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $totalIncidents = (int) ($currentSummary['total_incidents'] ?? 0);
        $avgDuration = (float) ($currentSummary['duree_moyenne_minutes'] ?? 0);
        $resolvedCount = (int) ($currentSummary['incidents_fermes'] ?? 0);
        $resolutionRate = $totalIncidents > 0 ? round(($resolvedCount / $totalIncidents) * 100, 1) : 0.0;

        $previousTotal = (int) ($previousSummary['total_incidents'] ?? 0);
        $previousAvgDuration = (float) ($previousSummary['duree_moyenne_minutes'] ?? 0);
        $previousResolvedCount = (int) ($previousSummary['incidents_fermes'] ?? 0);
        $previousResolutionRate = $previousTotal > 0 ? round(($previousResolvedCount / $previousTotal) * 100, 1) : 0.0;

        $incidentDelta = $this->percentageDelta($totalIncidents, $previousTotal);
        $avgDurationDelta = $this->percentageDelta($avgDuration, $previousAvgDuration);
        $resolutionDelta = round($resolutionRate - $previousResolutionRate, 1);

        $topDepart = $departStats->first();
        $topDepartName = $topDepart['nom'] ?? 'N/A';

        $topDepartCurrentCount = (int) ($topDepart['total'] ?? 0);
        $topDepartPreviousCount = (int) ($previousDepartStats->get($topDepart['id'] ?? null)['total'] ?? 0);
        $topDepartDelta = $this->percentageDelta($topDepartCurrentCount, $topDepartPreviousCount);

        $periodDays = collect();
        $cursor = $period->copy()->startOfMonth();
        $monthEnd = $period->copy()->endOfMonth();
        while ($cursor->lte($monthEnd)) {
            $periodDays->push($cursor->copy());
            $cursor->addDay();
        }

        $evolutionLabels = $periodDays->map(fn ($date) => $date->format('d/m'));
        $evolutionIncidentData = $periodDays->map(
            fn ($date) => (int) ($dailyRows->get($date->format('Y-m-d'))['total'] ?? 0)
        );
        $evolutionDurationData = $periodDays->map(
            fn ($date) => (float) ($dailyRows->get($date->format('Y-m-d'))['duree_moyenne_minutes'] ?? 0)
        );

        $typePalette = ['#ef2433', '#facc15', '#dc2626', '#f97316'];
        $typeTotal = max(1, (int) $byType->sum('total'));

        $cursorPercent = 0;
        $segments = [];
        foreach ($byType as $index => $typeItem) {
            $start = $cursorPercent;
            $cursorPercent += ($typeItem['total'] / $typeTotal) * 100;
            $segments[] = sprintf(
                '%s %.2f%% %.2f%%',
                $typePalette[$index % count($typePalette)],
                $start,
                $cursorPercent
            );
        }

        $typeDonutGradient = count($segments) > 0
            ? 'conic-gradient('.implode(', ', $segments).')'
            : 'conic-gradient(#e5e7eb 0% 100%)';

        $maxCauseCount = max(1, (int) $causeRows->max('total'));
        $causeBars = $causeRows->map(fn ($row) => [
            'label' => $row['libelle'],
            'total' => $row['total'],
            'percent' => round(($row['total'] / $maxCauseCount) * 100),
        ]);

        $maxDepartCount = max(1, (int) $departStats->max('total'));
        $criticalDepartRows = $departStats
            ->take(5)
            ->map(function ($row) use ($maxDepartCount) {
                $load = max(35, min(96, (int) round(($row['total'] / $maxDepartCount) * 100)));
                $networkStatus = $load >= 85 ? 'Critique' : ($load >= 60 ? 'Stable' : 'Optimal');

                return [
                    'code' => $row['code'],
                    'label' => $row['nom'],
                    'total' => $row['total'],
                    'network_status' => $networkStatus,
                    'load' => $load,
                ];
            })
            ->values();

        $periodOptions = collect(range(0, 11))->map(function ($offset) {
            $month = now()->startOfMonth()->subMonths($offset);

            return [
                'value' => $month->format('Y-m'),
                'label' => $month->format('m/Y'),
            ];
        });

        $exportQuery = array_filter([
            'month' => $period->format('Y-m'),
            'departement_id' => $filters['departement_id'],
            'cause_id' => $filters['cause_id'],
        ], fn ($value) => filled($value));

        return [
            'filters' => $filters,
            'periodOptions' => $periodOptions,
            'departements' => Departement::orderBy('nom')->get(['id', 'nom']),
            'causes' => Cause::orderBy('libelle')->get(['id', 'libelle']),
            'totalIncidents' => $totalIncidents,
            'avgDuration' => (int) round($avgDuration),
            'resolutionRate' => $resolutionRate,
            'topDepartName' => $topDepartName,
            'incidentDelta' => $incidentDelta,
            'avgDurationDelta' => $avgDurationDelta,
            'resolutionDelta' => $resolutionDelta,
            'topDepartDelta' => $topDepartDelta,
            'evolutionLabels' => $evolutionLabels,
            'evolutionIncidentData' => $evolutionIncidentData,
            'evolutionDurationData' => $evolutionDurationData,
            'byType' => $byType->map(fn ($row) => [
                'label' => $row['libelle'],
                'total' => $row['total'],
            ])->values(),
            'typePalette' => $typePalette,
            'typeDonutGradient' => $typeDonutGradient,
            'causeBars' => $causeBars,
            'criticalDepartRows' => $criticalDepartRows,
            'exportQuery' => $exportQuery,
        ];
    }

    private function percentageDelta(float|int $current, float|int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
