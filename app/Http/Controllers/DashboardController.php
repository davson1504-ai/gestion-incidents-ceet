<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\TypeIncident;
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
            'date_to'   => $request->input('date_to'),
        ];

        $base = Incident::with(['statut', 'priorite', 'typeIncident', 'departement'])
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->latest('date_debut');

        // KPIs
        $total      = (clone $base)->count();
        $openCount  = (clone $base)->reorder()->whereHas('statut', fn($q) => $q->where('is_final', false))->count();
        $closedCount= (clone $base)->reorder()->whereHas('statut', fn($q) => $q->where('is_final', true))->count();
        $avgDuration= (clone $base)->reorder()->whereNotNull('duree_minutes')->avg('duree_minutes');

        // Distribution par statut
        $byStatus = (clone $base)->reorder()
            ->selectRaw('status_id, count(*) as total')
            ->groupBy('status_id')
            ->get()
            ->map(function ($row) {
                $s = Statut::find($row->status_id);
                return [
                    'label' => $s?->libelle ?? 'N/A',
                    'color' => $s?->couleur ?? '#6c757d',
                    'total' => $row->total,
                ];
            });

        // Distribution par priorité
        $byPriorite = (clone $base)->reorder()
            ->selectRaw('priorite_id, count(*) as total')
            ->groupBy('priorite_id')
            ->get()
            ->map(function ($row) {
                $p = Priorite::find($row->priorite_id);
                return [
                    'label' => $p?->libelle ?? 'N/A',
                    'color' => $p?->couleur ?? '#e9ecef',
                    'total' => $row->total,
                ];
            });

        // Distribution par type
        $byType = (clone $base)->reorder()
            ->selectRaw('type_incident_id, count(*) as total')
            ->groupBy('type_incident_id')
            ->get()
            ->map(function ($row) {
                $t = TypeIncident::find($row->type_incident_id);
                return [
                    'label' => $t?->libelle ?? 'N/A',
                    'total' => $row->total,
                ];
            });

        // Top 5 départs
        $topDepart = (clone $base)->reorder()
            ->selectRaw('departement_id, count(*) as total')
            ->groupBy('departement_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'label' => optional($row->departement)->nom ?? 'N/A',
                    'total' => $row->total,
                ];
            });

        // Série temporelle 30 jours (par date_debut)
        $timeseries = (clone $base)->reorder()
            ->whereDate('date_debut', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('DATE(date_debut) as d, count(*) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return view('dashboard', [
            'filters'     => $filters,
            'kpis'        => compact('total', 'openCount', 'closedCount', 'avgDuration'),
            'byStatus'    => $byStatus,
            'byPriorite'  => $byPriorite,
            'byType'      => $byType,
            'topDepart'   => $topDepart,
            'timeseries'  => $timeseries,
        ]);
    }
}
