<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Statut;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function baseIncidentQuery(array $filters = []): Builder
    {
        $statusId = $filters['status_id'] ?? null;

        if (! $statusId && ! empty($filters['statut'])) {
            $statusId = Statut::query()->where('code', $filters['statut'])->value('id');
        }

        return Incident::query()
            ->filter(array_merge($filters, ['status_id' => $statusId]));
    }

    public function overview(array $filters = []): array
    {
        $query = $this->baseIncidentQuery($filters);

        $summary = (clone $query)
            ->selectRaw('COUNT(*) as total_incidents')
            ->selectRaw('AVG(duree_minutes) as duree_moyenne_minutes')
            ->selectRaw('SUM(CASE WHEN statuses.is_final = 0 THEN 1 ELSE 0 END) as incidents_ouverts')
            ->selectRaw('SUM(CASE WHEN statuses.is_final = 1 THEN 1 ELSE 0 END) as incidents_fermes')
            ->join('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->first();

        return [
            'total_incidents' => (int) ($summary?->total_incidents ?? 0),
            'duree_moyenne_minutes' => round((float) ($summary?->duree_moyenne_minutes ?? 0), 2),
            'incidents_ouverts' => (int) ($summary?->incidents_ouverts ?? 0),
            'incidents_fermes' => (int) ($summary?->incidents_fermes ?? 0),
        ];
    }

    public function byType(array $filters = []): array
    {
        return $this->baseIncidentQuery($filters)
            ->select('type_incidents.id', 'type_incidents.code', 'type_incidents.libelle')
            ->selectRaw('COUNT(*) as total')
            ->join('type_incidents', 'type_incidents.id', '=', 'incidents.type_incident_id')
            ->groupBy('type_incidents.id', 'type_incidents.code', 'type_incidents.libelle')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'code' => $row->code,
                'libelle' => $row->libelle,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    public function byCause(array $filters = []): array
    {
        return $this->baseIncidentQuery($filters)
            ->select('causes.id', 'causes.code', 'causes.libelle')
            ->selectRaw('COUNT(*) as total')
            ->leftJoin('causes', 'causes.id', '=', 'incidents.cause_id')
            ->groupBy('causes.id', 'causes.code', 'causes.libelle')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id ? (int) $row->id : null,
                'code' => $row->code,
                'libelle' => $row->libelle ?? 'Non renseignee',
                'total' => (int) $row->total,
            ])
            ->all();
    }

    public function byDepartement(array $filters = []): array
    {
        return $this->baseIncidentQuery($filters)
            ->select('departements.id', 'departements.code', 'departements.nom')
            ->selectRaw('COUNT(*) as total')
            ->join('departements', 'departements.id', '=', 'incidents.departement_id')
            ->groupBy('departements.id', 'departements.code', 'departements.nom')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'code' => $row->code,
                'nom' => $row->nom,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    public function daily(array $filters = []): array
    {
        return $this->baseIncidentQuery($filters)
            ->selectRaw('DATE(date_debut) as jour')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(duree_minutes) as duree_moyenne_minutes')
            ->groupBy(DB::raw('DATE(date_debut)'))
            ->orderBy('jour')
            ->get()
            ->map(fn ($row) => [
                'jour' => $row->jour,
                'total' => (int) $row->total,
                'duree_moyenne_minutes' => round((float) ($row->duree_moyenne_minutes ?? 0), 2),
            ])
            ->all();
    }

    public function monthly(array $filters = []): array
    {
        $monthExpression = $this->monthExpression();

        return $this->baseIncidentQuery($filters)
            ->selectRaw("{$monthExpression} as mois")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(duree_minutes) as duree_moyenne_minutes')
            ->groupBy(DB::raw($monthExpression))
            ->orderBy('mois')
            ->get()
            ->map(fn ($row) => [
                'mois' => $row->mois,
                'total' => (int) $row->total,
                'duree_moyenne_minutes' => round((float) ($row->duree_moyenne_minutes ?? 0), 2),
            ])
            ->all();
    }

    public function exportRows(array $filters = [], ?User $currentUser = null)
    {
        return $this->exportQuery($filters, $currentUser)->get();
    }

    public function exportQuery(array $filters = [], ?User $currentUser = null): Builder
    {
        return $this->baseIncidentQuery($filters)
            ->when($currentUser, fn (Builder $query) => $query->visibleToUser($currentUser))
            ->select([
                'incidents.id',
                'incidents.code_incident',
                'incidents.titre',
                'incidents.departement_id',
                'incidents.type_incident_id',
                'incidents.cause_id',
                'incidents.status_id',
                'incidents.priorite_id',
                'incidents.operateur_id',
                'incidents.responsable_id',
                'incidents.date_debut',
                'incidents.date_fin',
                'incidents.duree_minutes',
            ])
            ->with([
                'departement:id,nom',
                'typeIncident:id,libelle',
                'cause:id,libelle',
                'status:id,libelle',
                'priorite:id,libelle',
                'operateur:id,name',
                'responsable:id,name',
            ])
            ->orderByDesc('date_debut')
            ->orderByDesc('id');
    }

    private function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', date_debut)"
            : "DATE_FORMAT(date_debut, '%Y-%m')";
    }
}
