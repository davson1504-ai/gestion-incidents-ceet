<?php

namespace App\Services;

use App\Models\Incident;
use Illuminate\Support\Carbon;

class IncidentReportService
{
    public function dailyData(Carbon $date): array
    {
        $start = $date->copy()->startOfDay();
        $end   = $date->copy()->endOfDay();

        return $this->buildData($start, $end, 'day');
    }

    public function monthlyData(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        return $this->buildData($start, $end, 'month');
    }

    private function buildData(Carbon $start, Carbon $end, string $granularity): array
    {
        $base = Incident::with(['statut', 'priorite', 'departement', 'typeIncident', 'cause'])
            ->whereBetween('date_debut', [$start, $end])
            ->orderBy('date_debut');

        $incidents = $base->get();

        $total       = $incidents->count();
        $avgDuration = $incidents->whereNotNull('duree_minutes')->avg('duree_minutes');

        $byStatus = $incidents->groupBy('status_id')->map(fn ($g) => [
            'label' => optional($g->first()->statut)->libelle ?? 'N/A',
            'color' => optional($g->first()->statut)->couleur ?? '#6c757d',
            'total' => $g->count(),
        ])->values();

        $byPriorite = $incidents->groupBy('priorite_id')->map(fn ($g) => [
            'label' => optional($g->first()->priorite)->libelle ?? 'N/A',
            'color' => optional($g->first()->priorite)->couleur ?? '#e9ecef',
            'total' => $g->count(),
        ])->values();

        $byDepart = $incidents->groupBy('departement_id')->map(fn ($g) => [
            'label' => optional($g->first()->departement)->nom ?? 'N/A',
            'total' => $g->count(),
        ])->sortByDesc('total')->values();

        $byType = $incidents->groupBy('type_incident_id')->map(fn ($g) => [
            'label' => optional($g->first()->typeIncident)->libelle ?? 'N/A',
            'total' => $g->count(),
        ])->values();

        $byCause = $incidents->groupBy('cause_id')->map(fn ($g) => [
            'label' => optional($g->first()->cause)->libelle ?? 'N/A',
            'total' => $g->count(),
        ])->values();

        $topDepart = $byDepart->take(5);

        $timeseries = Incident::selectRaw('DATE(date_debut) as d, count(*) as total')
            ->whereBetween('date_debut', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return compact(
            'incidents',
            'total',
            'avgDuration',
            'byStatus',
            'byPriorite',
            'byDepart',
            'byType',
            'byCause',
            'topDepart',
            'timeseries',
            'start',
            'end',
            'granularity'
        );
    }
}

