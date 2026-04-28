<?php

namespace App\Services;

use App\Models\Incident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IncidentReportService
{
    public function dailyData(Carbon $date, array $filters = []): array
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        return $this->buildData($start, $end, 'day', $filters);
    }

    public function monthlyData(Carbon $month, array $filters = []): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        return $this->buildData($start, $end, 'month', $filters);
    }

    private function buildData(Carbon $start, Carbon $end, string $granularity, array $filters = []): array
    {
        $base = $this->baseQuery($start, $end, $filters);

        $summary = (clone $base)
            ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(incidents.duree_minutes) as avg_duration')
            ->selectRaw('SUM(CASE WHEN statuses.is_final = 1 THEN 1 ELSE 0 END) as closed_count')
            ->first();

        $total = (int) ($summary?->total ?? 0);
        $avgDuration = $summary?->avg_duration !== null ? (float) $summary->avg_duration : null;
        $closedCount = (int) ($summary?->closed_count ?? 0);
        $openCount = max(0, $total - $closedCount);

        $byStatus = $this->aggregateByStatus($base);
        $byPriorite = $this->aggregateByPriorite($base);
        $byDepart = $this->aggregateByDepartement($base);
        $byType = $this->aggregateByType($base);
        $byCause = $this->aggregateByCause($base);
        $topDepart = $byDepart->take(5)->values();

        $timeseries = collect((clone $base)
            ->selectRaw('DATE(date_debut) as d, COUNT(*) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->get())
            ->map(fn ($row) => [
                'd' => $row->d,
                'total' => (int) $row->total,
            ])
            ->values();

        $incidents = (clone $base)
            ->select([
                'incidents.id',
                'incidents.code_incident',
                'incidents.titre',
                'incidents.departement_id',
                'incidents.status_id',
                'incidents.priorite_id',
                'incidents.date_debut',
                'incidents.duree_minutes',
            ])
            ->with([
                'departement:id,nom',
                'status:id,libelle,couleur,is_final',
                'priorite:id,libelle,couleur,niveau',
            ])
            ->orderBy('date_debut')
            ->get();

        return compact(
            'incidents',
            'total',
            'avgDuration',
            'openCount',
            'closedCount',
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

    private function baseQuery(Carbon $start, Carbon $end, array $filters): Builder
    {
        $departementId = $filters['departement_id'] ?? null;
        $causeId = $filters['cause_id'] ?? null;

        return Incident::query()
            ->whereBetween('date_debut', [$start, $end])
            ->when($departementId, fn (Builder $query, $value) => $query->where('departement_id', $value))
            ->when($causeId, fn (Builder $query, $value) => $query->where('cause_id', $value));
    }

    private function aggregateByStatus(Builder $base): Collection
    {
        return collect((clone $base)
            ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->selectRaw('statuses.id, statuses.libelle, statuses.couleur, statuses.is_final, COUNT(*) as total')
            ->groupBy('statuses.id', 'statuses.libelle', 'statuses.couleur', 'statuses.is_final')
            ->orderByDesc('total')
            ->get())
            ->map(fn ($row) => [
                'label' => $row->libelle ?? 'N/A',
                'color' => $row->couleur ?? '#6c757d',
                'is_final' => (bool) ($row->is_final ?? false),
                'total' => (int) $row->total,
            ])
            ->values();
    }

    private function aggregateByPriorite(Builder $base): Collection
    {
        return collect((clone $base)
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
    }

    private function aggregateByDepartement(Builder $base): Collection
    {
        return collect((clone $base)
            ->leftJoin('departements', 'departements.id', '=', 'incidents.departement_id')
            ->selectRaw('departements.id, departements.nom, COUNT(*) as total')
            ->groupBy('departements.id', 'departements.nom')
            ->orderByDesc('total')
            ->get())
            ->map(fn ($row) => [
                'label' => $row->nom ?? 'N/A',
                'total' => (int) $row->total,
            ])
            ->values();
    }

    private function aggregateByType(Builder $base): Collection
    {
        return collect((clone $base)
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
    }

    private function aggregateByCause(Builder $base): Collection
    {
        return collect((clone $base)
            ->leftJoin('causes', 'causes.id', '=', 'incidents.cause_id')
            ->selectRaw('causes.id, causes.libelle, COUNT(*) as total')
            ->groupBy('causes.id', 'causes.libelle')
            ->orderByDesc('total')
            ->get())
            ->map(fn ($row) => [
                'label' => $row->libelle ?? 'N/A',
                'total' => (int) $row->total,
            ])
            ->values();
    }
}
