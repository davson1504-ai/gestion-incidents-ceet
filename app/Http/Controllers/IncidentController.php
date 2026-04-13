<?php

namespace App\Http\Controllers;

use App\Events\IncidentChanged;
use App\Exports\IncidentsExport;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Cause;
use App\Models\Departement;
use App\Models\Incident;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\TypeIncident;
use App\Models\User;
use App\Services\IncidentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class IncidentController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService)
    {
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
        $filters = $this->extractOpenIncidentFilters($request);
        $baseQuery = $this->openIncidentQuery($filters);

        $incidents = (clone $baseQuery)
            ->with(['departement', 'typeIncident', 'priorite', 'status'])
            ->paginate(20)
            ->withQueryString();

        $incidents->getCollection()->transform(fn (Incident $incident) => $this->withWaitingDuration($incident));

        $totalEnCours = (clone $baseQuery)->count();
        $critiquesCount = (clone $baseQuery)->where('priorites.niveau', 1)->count();
        $plusAncien = (clone $baseQuery)
            ->with(['departement', 'priorite', 'status'])
            ->first();

        if ($plusAncien) {
            $plusAncien = $this->withWaitingDuration($plusAncien);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'totalEnCours' => $totalEnCours,
                'critiquesCount' => $critiquesCount,
                'plusAncien' => $plusAncien ? [
                    'code_incident' => $plusAncien->code_incident,
                    'duree_minutes' => $plusAncien->duree_en_attente,
                    'label' => $this->formatDurationLabel($plusAncien->duree_en_attente),
                ] : null,
                'updatedAt' => now()->format('H:i:s'),
            ]);
        }

        return view('incidents.en-cours', [
            'incidents' => $incidents,
            'departements' => Departement::where('is_active', true)->orderBy('nom')->get(),
            'priorites' => Priorite::where('is_active', true)->orderBy('niveau')->get(),
            'filters' => $filters,
            'totalEnCours' => $totalEnCours,
            'critiquesCount' => $critiquesCount,
            'plusAncien' => $plusAncien,
        ]);
    }

    public function export(Request $request)
    {
        if (! $request->user()->can('incidents.view')) {
            abort(403);
        }

        $filters = $this->extractIncidentFilters($request);
        $format = (string) $request->input('format', 'csv');

        if ($format === 'excel') {
            return Excel::download(
                new IncidentsExport($filters),
                'incidents-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $rows = $this->baseIncidentQuery($filters)
            ->with(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur'])
            ->orderByDesc('date_debut')
            ->get();

        $callback = function () use ($rows): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

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

            foreach ($rows as $incident) {
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
            }

            fclose($output);
        };

        return response()->streamDownload($callback, 'incidents-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('incidents.create', [
            'departements' => Departement::where('is_active', true)->orderBy('nom')->get(),
            'statuts' => Statut::where('is_active', true)->orderBy('ordre')->get(),
            'priorites' => Priorite::where('is_active', true)->orderBy('niveau')->get(),
            'types' => TypeIncident::where('is_active', true)->orderBy('libelle')->get(),
            'causes' => Cause::where('is_active', true)->orderBy('libelle')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['code_incident'] = $this->incidentService->generateCode();
        $data['operateur_id'] = $userId;

        $incident = Incident::create($data);
        $this->incidentService->syncDurationOnClosure($incident);
        $this->incidentService->logAction(
            $incident,
            $userId,
            'create',
            "Création de l'incident",
            [],
            $incident->only($incident->getFillable())
        );
        $this->incidentService->logAudit($incident, $userId, 'create', ['message' => 'Incident créé']);
        broadcast(new IncidentChanged('created', $incident))->toOthers();

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident créé avec succès.');
    }

    public function show(Incident $incident): View
    {
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
        return view('incidents.edit', [
            'incident' => $incident,
            'departements' => Departement::where('is_active', true)->orderBy('nom')->get(),
            'statuts' => Statut::where('is_active', true)->orderBy('ordre')->get(),
            'priorites' => Priorite::where('is_active', true)->orderBy('niveau')->get(),
            'types' => TypeIncident::where('is_active', true)->orderBy('libelle')->get(),
            'causes' => Cause::where('is_active', true)->orderBy('libelle')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $userId = $request->user()->id;
        $oldValues = $incident->only($incident->getFillable());

        $incident->fill($request->validated());
        $incident->save();
        $this->incidentService->syncDurationOnClosure($incident);
        $this->incidentService->logAction(
            $incident,
            $userId,
            'update',
            "Mise à jour de l'incident",
            $oldValues,
            $incident->only($incident->getFillable())
        );
        $this->incidentService->logAudit($incident, $userId, 'update', ['message' => 'Incident mis à jour']);
        broadcast(new IncidentChanged('updated', $incident))->toOthers();

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident mis à jour avec succès.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $userId = auth()->id();

        $this->incidentService->logAction($incident, $userId, 'delete', "Suppression de l'incident");
        $this->incidentService->logAudit($incident, $userId, 'delete', ['message' => 'Incident supprimé']);
        broadcast(new IncidentChanged('deleted', $incident))->toOthers();

        $incident->delete();

        return redirect()->route('incidents.index')->with('success', 'Incident supprimé.');
    }

    private function renderIncidentList(Request $request, bool $onlyMine = false): View
    {
        $filters = $this->extractIncidentFilters($request);
        $currentUser = $request->user();
        $baseQuery = $this->baseIncidentQuery($filters, $onlyMine ? $currentUser : null);

        $incidents = (clone $baseQuery)
            ->with(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'superviseur'])
            ->latest('date_debut')
            ->paginate(15)
            ->withQueryString();

        $statRows = (clone $baseQuery)
            ->selectRaw('status_id, priorite_id, duree_minutes')
            ->get();

        $allStatuts = Statut::all()->keyBy('id');
        $allPriorites = Priorite::all()->keyBy('id');

        $byStatus = $statRows->groupBy('status_id')->map(function ($group, $statusId) use ($allStatuts) {
            $status = $allStatuts->get($statusId);

            return [
                'label' => $status?->libelle ?? 'Inconnu',
                'color' => $status?->couleur ?? '#6c757d',
                'total' => $group->count(),
            ];
        })->values();

        $byPriorite = $statRows->groupBy('priorite_id')->map(function ($group, $prioriteId) use ($allPriorites) {
            $priorite = $allPriorites->get($prioriteId);

            return [
                'label' => $priorite?->libelle ?? 'Inconnu',
                'color' => $priorite?->couleur ?? '#6c757d',
                'total' => $group->count(),
            ];
        })->values();

        $avgDuration = $statRows->whereNotNull('duree_minutes')->avg('duree_minutes');
        $openIds = $allStatuts->where('is_final', false)->pluck('id');
        $closedIds = $allStatuts->where('is_final', true)->pluck('id');
        $openCount = $statRows->whereIn('status_id', $openIds)->count();
        $closedCount = $statRows->whereIn('status_id', $closedIds)->count();

        return view('incidents.index', [
            'incidents' => $incidents,
            'departements' => Departement::orderBy('nom')->get(),
            'statuts' => Statut::orderBy('ordre')->get(),
            'priorites' => Priorite::orderBy('niveau')->get(),
            'types' => TypeIncident::orderBy('libelle')->get(),
            'causes' => Cause::orderBy('libelle')->get(),
            'operateurs' => User::active()->orderBy('name')->get(),
            'filters' => $filters,
            'listContext' => [
                'title' => $onlyMine ? 'Mes incidents' : 'Liste des incidents',
                'subtitle' => $onlyMine
                    ? 'Consultez les incidents qui vous sont attribués, supervisés ou déclarés.'
                    : "Consultez et gérez l'ensemble des anomalies détectées sur le réseau national.",
                'indexRoute' => $onlyMine ? 'incidents.mine' : 'incidents.index',
                'isMine' => $onlyMine,
            ],
            'stats' => [
                'byStatus' => $byStatus,
                'byPriorite' => $byPriorite,
                'avgDuration' => $avgDuration,
                'openCount' => $openCount,
                'closedCount' => $closedCount,
            ],
        ]);
    }

    private function extractIncidentFilters(Request $request): array
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
            $request->only([
                'departement_id',
                'status_id',
                'priorite_id',
                'type_incident_id',
                'cause_id',
                'operateur_id',
                'date_from',
                'date_to',
                'q',
            ])
        );
    }

    private function extractOpenIncidentFilters(Request $request): array
    {
        return array_merge(
            [
                'departement_id' => null,
                'priorite_id' => null,
                'date_from' => null,
                'date_to' => null,
                'q' => null,
            ],
            $request->only([
                'departement_id',
                'priorite_id',
                'date_from',
                'date_to',
                'q',
            ])
        );
    }

    private function baseIncidentQuery(array $filters, ?User $currentUser = null): Builder
    {
        return Incident::query()
            ->when($currentUser, function (Builder $query) use ($currentUser) {
                $query->where(function (Builder $incidentQuery) use ($currentUser) {
                    $incidentQuery
                        ->where('operateur_id', $currentUser->id)
                        ->orWhere('responsable_id', $currentUser->id)
                        ->orWhere('superviseur_id', $currentUser->id);
                });
            })
            ->when($filters['departement_id'], fn (Builder $query, $value) => $query->where('departement_id', $value))
            ->when($filters['status_id'], fn (Builder $query, $value) => $query->where('status_id', $value))
            ->when($filters['priorite_id'], fn (Builder $query, $value) => $query->where('priorite_id', $value))
            ->when($filters['type_incident_id'], fn (Builder $query, $value) => $query->where('type_incident_id', $value))
            ->when($filters['cause_id'], fn (Builder $query, $value) => $query->where('cause_id', $value))
            ->when($filters['operateur_id'], fn (Builder $query, $value) => $query->where('operateur_id', $value))
            ->when($filters['date_from'], fn (Builder $query, $value) => $query->whereDate('date_debut', '>=', $value))
            ->when($filters['date_to'], fn (Builder $query, $value) => $query->whereDate('date_debut', '<=', $value))
            ->when($filters['q'], function (Builder $query, $value) {
                $query->where(function (Builder $searchQuery) use ($value) {
                    $searchQuery
                        ->where('code_incident', 'like', "%{$value}%")
                        ->orWhere('titre', 'like', "%{$value}%");
                });
            });
    }

    private function openIncidentQuery(array $filters): Builder
    {
        return Incident::query()
            ->select('incidents.*')
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
            $parts[] = $days . 'j';
        }

        if ($hours > 0 || $days > 0) {
            $parts[] = $hours . 'h';
        }

        $parts[] = $remainingMinutes . 'min';

        return implode(' ', $parts);
    }
}
