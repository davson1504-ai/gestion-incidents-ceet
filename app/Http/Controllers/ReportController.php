<?php

namespace App\Http\Controllers;

use App\Exports\IncidentReportExport;
use App\Http\Requests\DailyReportRequest;
use App\Http\Requests\MonthlyReportRequest;
use App\Models\Cause;
use App\Models\Departement;
use App\Services\IncidentReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private IncidentReportService $service)
    {
        $this->middleware(['auth', 'verified', 'permission:reporting.view']);
    }

    public function index(Request $request): View
    {
        $period = $this->parseMonth((string) $request->input('period', now()->format('Y-m')));

        $filters = [
            'period' => $period->format('Y-m'),
            'departement_id' => $request->input('departement_id'),
            'cause_id' => $request->input('cause_id'),
        ];

        $serviceFilters = [
            'departement_id' => $filters['departement_id'],
            'cause_id' => $filters['cause_id'],
        ];

        $currentData = $this->service->monthlyData($period, $serviceFilters);
        $previousData = $this->service->monthlyData($period->copy()->subMonth(), $serviceFilters);

        $currentIncidents = $currentData['incidents'];
        $previousIncidents = $previousData['incidents'];

        $totalIncidents = (int) $currentData['total'];
        $avgDuration = (float) ($currentData['avgDuration'] ?? 0);

        $resolvedCount = $currentIncidents->filter(fn ($incident) => $incident->statut?->is_final)->count();
        $resolutionRate = $totalIncidents > 0 ? round(($resolvedCount / $totalIncidents) * 100, 1) : 0.0;

        $previousTotal = (int) $previousData['total'];
        $previousAvgDuration = (float) ($previousData['avgDuration'] ?? 0);
        $previousResolvedCount = $previousIncidents->filter(fn ($incident) => $incident->statut?->is_final)->count();
        $previousResolutionRate = $previousTotal > 0 ? round(($previousResolvedCount / $previousTotal) * 100, 1) : 0.0;

        $incidentDelta = $this->percentageDelta($totalIncidents, $previousTotal);
        $avgDurationDelta = $this->percentageDelta($avgDuration, $previousAvgDuration);
        $resolutionDelta = round($resolutionRate - $previousResolutionRate, 1);

        $departStats = $currentIncidents
            ->groupBy('departement_id')
            ->map(function ($group) {
                $departement = $group->first()->departement;

                return [
                    'id' => $departement?->id,
                    'code' => $departement?->code ?? 'N/A',
                    'label' => $departement?->nom ?? 'N/A',
                    'total' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $topDepart = $departStats->first();
        $topDepartName = $topDepart['label'] ?? 'N/A';

        $topDepartCurrentCount = (int) ($topDepart['total'] ?? 0);
        $topDepartPreviousCount = 0;
        if ($topDepart && isset($topDepart['id'])) {
            $topDepartPreviousCount = $previousIncidents
                ->filter(fn ($incident) => $incident->departement_id === $topDepart['id'])
                ->count();
        }
        $topDepartDelta = $this->percentageDelta($topDepartCurrentCount, $topDepartPreviousCount);

        $periodDays = collect();
        $cursor = $period->copy()->startOfMonth();
        $monthEnd = $period->copy()->endOfMonth();
        while ($cursor->lte($monthEnd)) {
            $periodDays->push($cursor->copy());
            $cursor->addDay();
        }

        $countByDay = $currentIncidents
            ->groupBy(fn ($incident) => $incident->date_debut?->format('Y-m-d'))
            ->map(fn ($items) => $items->count());

        $avgByDay = $currentIncidents
            ->filter(fn ($incident) => $incident->duree_minutes !== null)
            ->groupBy(fn ($incident) => $incident->date_debut?->format('Y-m-d'))
            ->map(fn ($items) => round((float) $items->avg('duree_minutes'), 1));

        $evolutionLabels = $periodDays->map(fn ($date) => $date->format('d/m'));
        $evolutionIncidentData = $periodDays->map(fn ($date) => (int) ($countByDay[$date->format('Y-m-d')] ?? 0));
        $evolutionDurationData = $periodDays->map(fn ($date) => (float) ($avgByDay[$date->format('Y-m-d')] ?? 0));

        $byType = collect($currentData['byType'])
            ->sortByDesc('total')
            ->take(4)
            ->values();

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

        $causeRows = collect($currentData['byCause'])
            ->sortByDesc('total')
            ->take(5)
            ->values();
        $maxCauseCount = max(1, (int) $causeRows->max('total'));

        $causeBars = $causeRows->map(fn ($row) => [
            'label' => $row['label'],
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
                    'label' => $row['label'],
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

        return view('reports.index', [
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
            'byType' => $byType,
            'typePalette' => $typePalette,
            'typeDonutGradient' => $typeDonutGradient,
            'causeBars' => $causeBars,
            'criticalDepartRows' => $criticalDepartRows,
            'exportQuery' => $exportQuery,
        ]);
    }

    public function exportDailyReport(DailyReportRequest $request)
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));
        $filters = $request->only(['departement_id', 'cause_id']);
        $data = $this->service->dailyData($date, $filters);

        return $this->export($request, $data, "rapport-journalier-{$date->format('Y-m-d')}");
    }

    public function exportMonthlyReport(MonthlyReportRequest $request)
    {
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->format('Y-m')));
        $filters = $request->only(['departement_id', 'cause_id']);
        $data = $this->service->monthlyData($month, $filters);

        return $this->export($request, $data, "rapport-mensuel-{$month->format('Y-m')}");
    }

    private function export(Request $request, array $data, string $baseName)
    {
        $format = $request->input('format', 'pdf');

        if ($format === 'excel') {
            return Excel::download(new IncidentReportExport($data), "{$baseName}.xlsx");
        }

        $pdf = Pdf::loadView('reports.incidents', $data)->setPaper('a4', 'portrait');

        return $pdf->download("{$baseName}.pdf");
    }

    private function parseMonth(string $value): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    private function percentageDelta(float|int $current, float|int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
