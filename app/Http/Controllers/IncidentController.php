<?php

namespace App\Http\Controllers;

use App\Events\IncidentChanged;
use App\Exports\IncidentsExport;
use App\Http\Requests\Api\AssignIncidentRequest;
use App\Http\Requests\Api\CloseIncidentRequest;
use App\Http\Requests\Api\StoreInterventionRequest;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Incident;
use App\Services\IncidentCatalogueService;
use App\Services\IncidentQueryService;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class IncidentController extends Controller
{
    public function __construct(
        private readonly IncidentService $incidentService,
        private readonly IncidentCatalogueService $incidentCatalogueService,
        private readonly IncidentQueryService $incidentQueryService,
    ) {
        $this->middleware('permission:incidents.view|incidents.view.assigned')->only(['index', 'mine', 'show', 'enCours']);
        $this->middleware('permission:incidents.export')->only(['export']);
        $this->middleware('permission:incidents.create')->only(['create', 'store']);
        $this->middleware('permission:incidents.update')->only(['edit', 'update']);
        $this->middleware('permission:incidents.delete')->only(['destroy']);
    }

    public function index(Request $request): View|RedirectResponse
    {
        return $this->renderIncidentList($request);
    }

    public function mine(Request $request): View
    {
        return $this->renderIncidentList($request, true, 'incidents.mine');
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

        $this->broadcastIncidentChanged('created', $incident);

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
            'interventions.user',
            'report.user',
            'report.operateur',
            'report.validator',
            'report.refuser',
        ]);

        return view('incidents.show', array_merge(
            ['incident' => $incident],
            $this->incidentCatalogueService->activeFormCatalogues()
        ));
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

        $this->broadcastIncidentChanged('updated', $incident);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident mis a jour avec succes.');
    }

    public function assign(AssignIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $updated = $this->incidentService->assignIncident($incident, $request->validated(), $request->user());

        $this->broadcastIncidentChanged('assigned', $updated);

        return redirect()
            ->route('incidents.show', $updated)
            ->with('success', 'Incident assigne avec succes.');
    }

    public function storeIntervention(StoreInterventionRequest $request, Incident $incident): RedirectResponse
    {
        $this->authorize('take', $incident);

        $intervention = $this->incidentService->addIntervention($incident, $request->validated(), $request->user());

        $this->broadcastIncidentChanged('intervention', $intervention->incident);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Intervention enregistree avec succes.');
    }

    public function take(StoreInterventionRequest $request, Incident $incident): RedirectResponse
    {
        return $this->storeIntervention($request, $incident);
    }

    public function resolve(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('resolve', $incident);

        $payload = $request->validate([
            'actions_menees' => ['nullable', 'string'],
            'resultat' => ['required', 'string'],
            'resolution_summary' => ['nullable', 'string'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $updated = $this->incidentService->resolveIncident($incident, $payload, $request->user());
        $this->broadcastIncidentChanged('resolved', $updated);

        return redirect()
            ->route('incidents.show', $updated)
            ->with('success', 'Incident marque comme resolu.');
    }

    public function report(Request $request, Incident $incident): RedirectResponse
    {
        return $this->submitReport($request, $incident);
    }

    public function submitReport(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('report', $incident);

        $payload = $request->validate([
            'actions_realisees' => ['required', 'string'],
            'resultat' => ['required', 'string'],
            'observations' => ['nullable', 'string'],
            'submitted_at' => ['nullable', 'date'],
        ]);

        $report = $this->incidentService->submitReport($incident, $payload, $request->user());
        $this->broadcastIncidentChanged('reported', $report->incident);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Rapport d intervention soumis au controle.');
    }

    public function editRejectedReport(Incident $incident): RedirectResponse
    {
        $this->authorize('report', $incident);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Corrigez le rapport refuse puis soumettez-le a nouveau.');
    }

    public function updateReport(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('report', $incident);

        $payload = $request->validate([
            'actions_realisees' => ['required', 'string'],
            'resultat' => ['required', 'string'],
            'observations' => ['nullable', 'string'],
            'submitted_at' => ['nullable', 'date'],
        ]);

        $report = $this->incidentService->updateRejectedReport($incident, $payload, $request->user());
        $this->broadcastIncidentChanged('reported', $report->incident);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Rapport corrige et soumis a nouveau.');
    }

    public function validateResolution(Request $request, Incident $incident): RedirectResponse
    {
        return $this->validateReport($request, $incident);
    }

    public function validateReport(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('validateResolution', $incident);

        $updated = $this->incidentService->validateReport($incident, $request->user());
        $this->broadcastIncidentChanged('validated', $updated);

        return redirect()
            ->route('incidents.show', $updated)
            ->with('success', 'Rapport d intervention valide. L incident peut etre cloture.');
    }

    public function rejectReport(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('rejectReport', $incident);

        $payload = $request->validate([
            'motif_refus' => ['required', 'string', 'min:3'],
        ]);

        $updated = $this->incidentService->rejectReport($incident, $payload['motif_refus'], $request->user());
        $this->broadcastIncidentChanged('report_rejected', $updated);

        return redirect()
            ->route('incidents.show', $updated)
            ->with('success', 'Rapport refuse. L operateur a ete notifie.');
    }

    public function close(CloseIncidentRequest $request, Incident $incident): RedirectResponse
    {
        return $this->closeIncident($request, $incident);
    }

    public function closeIncident(CloseIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $closed = $this->incidentService->closeIncident($incident, $request->validated(), $request->user());

        $this->broadcastIncidentChanged('closed', $closed);

        return redirect()
            ->route('incidents.show', $closed)
            ->with('success', 'Incident cloture avec succes.');
    }

    public function destroy(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('delete', $incident);

        $this->broadcastIncidentChanged('deleted', $incident);

        $this->incidentService->deleteIncident($incident, $request->user());

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident supprime.');
    }

    private function renderIncidentList(Request $request, bool $onlyMine = false, string $view = 'incidents.index'): View
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

        $listPayload = $onlyMine
            ? $this->incidentQueryService->listHandledIncidents($filters, $request->user(), 15)
            : $this->incidentQueryService->listIncidents($filters, $request->user(), 15);

        return view($view, array_merge([
            'incidents' => $listPayload['incidents'],
            'filters' => $filters,
            'listContext' => [
                'title' => $onlyMine ? 'Mes traitements' : 'Liste des incidents',
                'subtitle' => $onlyMine
                    ? 'Consultez les incidents sur lesquels vous avez enregistre une intervention ou finalise une prise en charge.'
                    : "Consultez et gerez l'ensemble des anomalies detectees sur le reseau national.",
                'indexRoute' => $onlyMine ? 'incidents.mine' : 'incidents.index',
                'isMine' => $onlyMine,
            ],
            'stats' => $listPayload['stats'],
        ], $this->incidentCatalogueService->listingCatalogues()));
    }

    private function broadcastIncidentChanged(string $action, Incident $incident): void
    {
        try {
            $pendingBroadcast = broadcast(new IncidentChanged($action, $incident))->toOthers();
            unset($pendingBroadcast);
        } catch (Throwable $exception) {
            Log::warning('Incident broadcast skipped.', [
                'action' => $action,
                'incident_id' => $incident->id,
                'broadcast_connection' => config('broadcasting.default'),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
