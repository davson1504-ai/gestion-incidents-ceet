<?php

namespace App\Http\Controllers;

use App\Events\IncidentChanged;
use App\Exports\IncidentsExport;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Incident;
use App\Services\IncidentCatalogueService;
use App\Services\IncidentQueryService;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class IncidentController extends Controller
{
    public function __construct(
        private readonly IncidentService $incidentService,
        private readonly IncidentCatalogueService $incidentCatalogueService,
        private readonly IncidentQueryService $incidentQueryService,
    ) {
        $this->middleware('permission:incidents.view')->only(['index', 'mine', 'show', 'export', 'enCours']);
        $this->middleware('permission:incidents.create')->only(['create', 'store']);
        $this->middleware('permission:incidents.update')->only(['edit', 'update']);
        $this->middleware('permission:incidents.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        return $this->renderIncidentList($request);
    }

    public function mine(Request $request): View
    {
        return $this->renderIncidentList($request, true);
    }

    public function enCours(Request $request): View|JsonResponse
    {
        $filters = $this->incidentQueryService->defaultOpenIncidentFilters($request->only([
            'departement_id',
            'priorite_id',
            'date_from',
            'date_to',
            'q',
        ]));

        $payload = $this->incidentQueryService->listOpenIncidents($filters, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'totalEnCours' => $payload['totalEnCours'],
                'critiquesCount' => $payload['critiquesCount'],
                'plusAncien' => $payload['plusAncienSummary'],
                'incidents' => $payload['incidents']->getCollection()->map(fn (Incident $incident) => [
                    'id' => $incident->id,
                    'code_incident' => $incident->code_incident,
                    'titre' => $incident->titre,
                    'date_debut' => $incident->date_debut?->toIso8601String(),
                    'departement' => $incident->departement ? [
                        'id' => $incident->departement->id,
                        'nom' => $incident->departement->nom,
                    ] : null,
                    'priorite' => $incident->priorite ? [
                        'id' => $incident->priorite->id,
                        'libelle' => $incident->priorite->libelle,
                        'niveau' => $incident->priorite->niveau,
                    ] : null,
                    'statut' => $incident->status ? [
                        'id' => $incident->status->id,
                        'libelle' => $incident->status->libelle,
                        'is_final' => (bool) $incident->status->is_final,
                    ] : null,
                ])->values(),
                'updatedAt' => now()->format('H:i:s'),
            ]);
        }

        return view('incidents.en-cours', array_merge([
            'incidents' => $payload['incidents'],
            'filters' => $filters,
            'totalEnCours' => $payload['totalEnCours'],
            'critiquesCount' => $payload['critiquesCount'],
            'plusAncien' => $payload['plusAncien'],
        ], $this->incidentCatalogueService->openIncidentCatalogues()));
    }

    public function export(Request $request)
    {
        if (! $request->user()->can('incidents.export')) {
            abort(403);
        }

        $filters = $this->incidentQueryService->defaultIncidentFilters($request->only([
            'departement_id',
            'status_id',
            'priorite_id',
            'type_incident_id',
            'cause_id',
            'operateur_id',
            'date_from',
            'date_to',
            'q',
        ]));

        $format = (string) $request->input('format', 'csv');
        $currentUser = $request->user();

        if ($format === 'excel') {
            return Excel::download(
                new IncidentsExport($filters, $currentUser),
                'incidents-'.now()->format('Y-m-d').'.xlsx'
            );
        }

        // CSV: Streaming par cursor pour zéro mémoire
        $callback = function () use ($filters, $currentUser): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($output, [
                'Code',
                'Titre',
                'Département',
                'Statut',
                'Priorité',
                'Type',
                'Cause',
                'Début',
                'Fin',
                'Durée (min)',
                'Opérateur',
            ], ';');

            // cursor() itère par batch sans charger tout en mémoire
            $this->incidentQueryService->baseIncidentQuery($filters, $currentUser)
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
                ])
                ->orderByDesc('incidents.date_debut')
                ->orderByDesc('incidents.id')
                ->lazy(200)
                ->each(function ($incident) use ($output) {
                    fputcsv($output, [
                        $incident->code_incident,
                        $incident->titre,
                        optional($incident->departement)->nom,
                        optional($incident->status)->libelle,
                        optional($incident->priorite)->libelle,
                        optional($incident->typeIncident)->libelle,
                        optional($incident->cause)->libelle,
                        optional($incident->date_debut)?->format('d/m/Y H:i'),
                        optional($incident->date_fin)?->format('d/m/Y H:i'),
                        $incident->duree_minutes,
                        optional($incident->operateur)->name,
                    ], ';');
                });

            fclose($output);
        };

        return response()->streamDownload($callback, 'incidents-export-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('incidents.create', $this->incidentCatalogueService->activeFormCatalogues());
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $incident = $this->incidentService->createIncident($request->validated(), $request->user());

        broadcast(new IncidentChanged('created', $incident))->toOthers();

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident cree avec succes.');
    }

    public function show(Incident $incident): View
    {
        $this->authorize('view', $incident);

        $incident->load([
            'departement',
            'typeIncident',
            'cause',
            'status',
            'priorite',
            'operateur',
            'responsable',
            'superviseur',
            'actions.user',
        ]);

        return view('incidents.show', compact('incident'));
    }

    public function edit(Incident $incident): View
    {
        $this->authorize('update', $incident);

        return view('incidents.edit', array_merge(
            ['incident' => $incident],
            $this->incidentCatalogueService->activeFormCatalogues()
        ));
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $this->authorize('update', $incident);

        $incident = $this->incidentService->updateIncident($incident, $request->validated(), $request->user());

        broadcast(new IncidentChanged('updated', $incident))->toOthers();

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident mis a jour avec succes.');
    }

    public function destroy(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('delete', $incident);

        broadcast(new IncidentChanged('deleted', $incident))->toOthers();

        $this->incidentService->deleteIncident($incident, $request->user());

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident supprime.');
    }

    private function renderIncidentList(Request $request, bool $onlyMine = false): View
    {
        $filters = $this->incidentQueryService->defaultIncidentFilters($request->only([
            'departement_id',
            'status_id',
            'priorite_id',
            'type_incident_id',
            'cause_id',
            'operateur_id',
            'date_from',
            'date_to',
            'q',
        ]));

        $listPayload = $this->incidentQueryService->listIncidents(
            $filters,
            $request->user(),
            15
        );

        return view('incidents.index', array_merge([
            'incidents' => $listPayload['incidents'],
            'filters' => $filters,
            'listContext' => [
                'title' => $onlyMine ? 'Mes incidents' : 'Liste des incidents',
                'subtitle' => $onlyMine
                    ? 'Consultez les incidents qui vous sont attribues, supervises ou declares.'
                    : "Consultez et gerez l'ensemble des anomalies detectees sur le reseau national.",
                'indexRoute' => $onlyMine ? 'incidents.mine' : 'incidents.index',
                'isMine' => $onlyMine,
            ],
            'stats' => $listPayload['stats'],
        ], $this->incidentCatalogueService->listingCatalogues()));
    }
}
