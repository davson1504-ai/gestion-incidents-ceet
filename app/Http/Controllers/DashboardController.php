<?php

namespace App\Http\Controllers;

use App\Models\Incident;
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

        // ── Requête de base sans order ─────────────────────────────────
        $base = Incident::query()
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'],   fn ($q, $v) => $q->whereDate('date_debut', '<=', $v));

        // ── Tous les incidents filtrés (une seule requête) ─────────────
        $rows = (clone $base)
            ->with(['statut', 'priorite', 'typeIncident', 'departement', 'cause'])
            ->latest('date_debut')
            ->get();

        // ── KPIs calculés en PHP ───────────────────────────────────────
        $total       = $rows->count();
        $openCount   = $rows->filter(fn ($i) => $i->statut && ! $i->statut->is_final)->count();
        $closedCount = $rows->filter(fn ($i) => $i->statut &&   $i->statut->is_final)->count();
        $avgDuration = $rows->whereNotNull('duree_minutes')->avg('duree_minutes');

        // ── Distributions ──────────────────────────────────────────────
        $byStatus = $rows->groupBy('status_id')->map(function ($g) {
            $s = $g->first()->statut;
            return ['label' => $s?->libelle ?? 'N/A', 'color' => $s?->couleur ?? '#6c757d', 'total' => $g->count()];
        })->values();

        $byPriorite = $rows->groupBy('priorite_id')->map(function ($g) {
            $p = $g->first()->priorite;
            return ['label' => $p?->libelle ?? 'N/A', 'color' => $p?->couleur ?? '#e9ecef', 'total' => $g->count()];
        })->values();

        $byType = $rows->groupBy('type_incident_id')->map(function ($g) {
            $t = $g->first()->typeIncident;
            return ['label' => $t?->libelle ?? 'N/A', 'total' => $g->count()];
        })->values();

        $byCause = $rows->groupBy('cause_id')->map(function ($g) {
            $c = $g->first()->cause;
            return ['label' => $c?->libelle ?? 'N/A', 'total' => $g->count()];
        })->sortByDesc('total')->take(10)->values();

        $topDepart = $rows->groupBy('departement_id')->map(function ($g) {
            return ['label' => optional($g->first()->departement)->nom ?? 'N/A', 'total' => $g->count()];
        })->sortByDesc('total')->take(5)->values();

        // ── Série temporelle 30 derniers jours (1 seule requête SQL) ──
        $timeseries = Incident::query()
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'],   fn ($q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->when(! $filters['date_from'], fn ($q) => $q->whereDate('date_debut', '>=', now()->subDays(30)->toDateString()))
            ->selectRaw('DATE(date_debut) as d, count(*) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return view('dashboard', [
            'filters'    => $filters,
            'kpis'       => compact('total', 'openCount', 'closedCount', 'avgDuration'),
            'byStatus'   => $byStatus,
            'byPriorite' => $byPriorite,
            'byType'     => $byType,
            'byCause'    => $byCause,
            'topDepart'  => $topDepart,
            'timeseries' => $timeseries,
        ]);
    }
}
