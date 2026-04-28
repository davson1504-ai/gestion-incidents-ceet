<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IncidentQueryService
{
    public function defaultIncidentFilters(array $input): array
    {
        return array_merge(
            [
                'departement_id' => null,
                'status_id' => null,
                'priorite_id' => null,
                'type_incident_id' => null,
                'cause_id' => null,
                'operateur_id' => null,
                'date_from' => null,
                'date_to' => null,
                'q' => null,
            ],
            $input
        );
    }

    public function defaultOpenIncidentFilters(array $input): array
    {
        return array_merge(
            [
                'departement_id' => null,
                'priorite_id' => null,
                'date_from' => null,
                'date_to' => null,
                'q' => null,
            ],
            $input
        );
    }

    public function listIncidents(array $filters, ?User $currentUser = null, int $perPage = 15): array
    {
        $baseQuery = $this->baseIncidentQuery($filters, $currentUser);

        $incidents = (clone $baseQuery)
            ->with(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'superviseur'])
            ->latest('date_debut')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'incidents' => $incidents,
            'stats' => $this->buildStats($baseQuery),
        ];
    }

    public function listOpenIncidents(array $filters, ?User $currentUser = null, int $perPage = 20): array
    {
        $baseQuery = $this->openIncidentQuery($filters, $currentUser);

        $paginatedIncidents = (clone $baseQuery)
            ->with(['departement', 'typeIncident', 'priorite', 'status'])
            ->paginate($perPage)
            ->withQueryString();

        $paginatedIncidents->getCollection()->transform(
            fn (Incident $incident) => $this->withWaitingDuration($incident)
        );

        $totalEnCours = $paginatedIncidents->total();

        $plusAncien = (clone $baseQuery)
            ->with(['departement', 'priorite', 'status'])
            ->first();

        if ($plusAncien) {
            $plusAncien = $this->withWaitingDuration($plusAncien);
        }

        $critiquesCount = (clone $baseQuery)
            ->where('priorites.niveau', 1)
            ->count();

        return [
            'incidents' => $paginatedIncidents,
            'totalEnCours' => $totalEnCours,
            'critiquesCount' => $critiquesCount,
            'plusAncien' => $plusAncien,
            'plusAncienSummary' => $plusAncien ? [
                'code_incident' => $plusAncien->code_incident,
                'duree_minutes' => $plusAncien->duree_en_attente,
                'label' => $this->formatDurationLabel($plusAncien->duree_en_attente),
            ] : null,
        ];
    }

    public function exportRows(array $filters): Collection
    {
        return $this->baseIncidentQuery($filters)
            ->with(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur'])
            ->orderByDesc('date_debut')
            ->get();
    }

    public function baseIncidentQuery(array $filters, ?User $currentUser = null): Builder
    {
        return Incident::query()
            ->when($currentUser, fn (Builder $query) => $query->visibleToUser($currentUser))
            ->filter($filters);
    }

    private function openIncidentQuery(array $filters, ?User $currentUser = null): Builder
    {
        return Incident::query()
            ->select('incidents.*')
            ->when($currentUser, fn (Builder $query) => $query->visibleToUser($currentUser))
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->leftJoin('priorites', 'priorites.id', '=', 'incidents.priorite_id')
            ->where('statuses.is_final', false)
            ->when($filters['departement_id'], fn (Builder $query, $value) => $query->where('incidents.departement_id', $value))
            ->when($filters['priorite_id'], fn (Builder $query, $value) => $query->where('incidents.priorite_id', $value))
            ->when($filters['date_from'], fn (Builder $query, $value) => $query->whereDate('incidents.date_debut', '>=', $value))
            ->when($filters['date_to'], fn (Builder $query, $value) => $query->whereDate('incidents.date_debut', '<=', $value))
            ->when($filters['q'], function (Builder $query, $value) {
                $query->where(function (Builder $searchQuery) use ($value) {
                    $searchQuery
                        ->where('incidents.code_incident', 'like', "%{$value}%")
                        ->orWhere('incidents.titre', 'like', "%{$value}%");
                });
            })
            ->orderByRaw('CASE WHEN priorites.niveau IS NULL THEN 999 ELSE priorites.niveau END ASC')
            ->orderBy('incidents.date_debut');
    }

    private function buildStats(Builder $baseQuery): array
    {
        $summary = (clone $baseQuery)
            ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->selectRaw(
                '
                AVG(incidents.duree_minutes) as avg_duration,
                SUM(CASE WHEN statuses.is_final = 0 OR statuses.is_final IS NULL THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN statuses.is_final = 1 THEN 1 ELSE 0 END) as closed_count
                '
            )
            ->first();

        $byStatus = collect((clone $baseQuery)
            ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->selectRaw('statuses.id, statuses.libelle, statuses.couleur, COUNT(*) as total')
            ->groupBy('statuses.id', 'statuses.libelle', 'statuses.couleur')
            ->orderByDesc('total')
            ->get())
            ->map(fn ($row) => [
                'label' => $row->libelle ?? 'Inconnu',
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
                'label' => $row->libelle ?? 'Inconnu',
                'color' => $row->couleur ?? '#6c757d',
                'total' => (int) $row->total,
            ])
            ->values();

        return [
            'byStatus' => $byStatus,
            'byPriorite' => $byPriorite,
            'avgDuration' => $summary?->avg_duration !== null ? (float) $summary->avg_duration : null,
            'openCount' => (int) ($summary?->open_count ?? 0),
            'closedCount' => (int) ($summary?->closed_count ?? 0),
        ];
    }

    private function withWaitingDuration(Incident $incident): Incident
    {
        $incident->duree_en_attente = $incident->date_debut
            ? $incident->date_debut->diffInMinutes(now())
            : null;

        return $incident;
    }

    private function formatDurationLabel(?int $minutes): string
    {
        if ($minutes === null) {
            return '-';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remainingMinutes = $minutes % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days.'j';
        }

        if ($hours > 0 || $days > 0) {
            $parts[] = $hours.'h';
        }

        $parts[] = $remainingMinutes.'min';

        return implode(' ', $parts);
    }
}
