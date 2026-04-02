<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Cause;
use App\Models\Departement;
use App\Models\Incident;
use App\Models\IncidentAction;
use App\Models\Log;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\TypeIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:incidents.view'])->only(['index', 'show']);
        $this->middleware(['permission:incidents.create'])->only(['create', 'store']);
        $this->middleware(['permission:incidents.update'])->only(['edit', 'update']);
        $this->middleware(['permission:incidents.delete'])->only(['destroy']);
        $this->middleware(['permission:incidents.view'])->only(['export']);
    }

    public function index(Request $request): View
    {
        $filters = array_merge(
            [
                'departement_id'   => null,
                'status_id'        => null,
                'priorite_id'      => null,
                'type_incident_id' => null,
                'date_from'        => null,
                'date_to'          => null,
                'q'                => null,
            ],
            $request->only([
                'departement_id',
                'status_id',
                'priorite_id',
                'type_incident_id',
                'date_from',
                'date_to',
                'q',
            ])
        );

        $baseQuery = Incident::with([
            'departement',
            'typeIncident',
            'cause',
            'statut',
            'priorite',
            'operateur',
            'superviseur',
        ])
            ->when($filters['departement_id'] ?? null, fn ($q, $v) => $q->where('departement_id', $v))
            ->when($filters['status_id'] ?? null, fn ($q, $v) => $q->where('status_id', $v))
            ->when($filters['priorite_id'] ?? null, fn ($q, $v) => $q->where('priorite_id', $v))
            ->when($filters['type_incident_id'] ?? null, fn ($q, $v) => $q->where('type_incident_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->when($filters['q'] ?? null, function ($q, $v) {
                $q->where(function ($sq) use ($v) {
                    $sq->where('code_incident', 'like', "%{$v}%")
                        ->orWhere('titre', 'like', "%{$v}%");
                });
            })
            ->latest('date_debut');

        $incidents = $baseQuery->paginate(15)->withQueryString();

        // Stats pour tableau de bord (filtrés comme la liste)
        $byStatus = (clone $baseQuery)->reorder() // supprime les ORDER BY hérités pour éviter only_full_group_by
            ->selectRaw('status_id, count(*) as total')
            ->groupBy('status_id')
            ->get()
            ->map(function ($row) {
                $statut = Statut::find($row->status_id);
                return [
                    'label' => $statut?->libelle ?? 'Inconnu',
                    'color' => $statut?->couleur ?? '#6c757d',
                    'total' => $row->total,
                ];
            });

        $byPriorite = (clone $baseQuery)->reorder()
            ->selectRaw('priorite_id, count(*) as total')
            ->groupBy('priorite_id')
            ->get()
            ->map(function ($row) {
                $priorite = Priorite::find($row->priorite_id);
                return [
                    'label' => $priorite?->libelle ?? 'Inconnu',
                    'color' => $priorite?->couleur ?? '#6c757d',
                    'total' => $row->total,
                ];
            });

        $avgDuration = (clone $baseQuery)->reorder()->whereNotNull('duree_minutes')->avg('duree_minutes');

        $openCount = (clone $baseQuery)->reorder()->whereHas('statut', fn ($q) => $q->where('is_final', false))->count();
        $closedCount = (clone $baseQuery)->reorder()->whereHas('statut', fn ($q) => $q->where('is_final', true))->count();

        return view('incidents.index', [
            'incidents'    => $incidents,
            'departements' => Departement::orderBy('nom')->get(),
            'statuts'      => Statut::orderBy('ordre')->get(),
            'priorites'    => Priorite::orderBy('niveau')->get(),
            'types'        => TypeIncident::orderBy('libelle')->get(),
            'filters'      => $filters,
            'stats'        => [
                'byStatus'     => $byStatus,
                'byPriorite'   => $byPriorite,
                'avgDuration'  => $avgDuration,
                'openCount'    => $openCount,
                'closedCount'  => $closedCount,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'departement_id','status_id','priorite_id','type_incident_id','date_from','date_to','q'
        ]);

        $query = Incident::with(['departement','typeIncident','cause','statut','priorite','operateur'])
            ->when($filters['departement_id'] ?? null, fn ($q, $v) => $q->where('departement_id', $v))
            ->when($filters['status_id'] ?? null, fn ($q, $v) => $q->where('status_id', $v))
            ->when($filters['priorite_id'] ?? null, fn ($q, $v) => $q->where('priorite_id', $v))
            ->when($filters['type_incident_id'] ?? null, fn ($q, $v) => $q->where('type_incident_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->when($filters['q'] ?? null, function ($q, $v) {
                $q->where(function ($sq) use ($v) {
                    $sq->where('code_incident', 'like', "%{$v}%")
                        ->orWhere('titre', 'like', "%{$v}%");
                });
            })
            ->orderByDesc('date_debut');

        $rows = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="incidents.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Code', 'Titre', 'Département', 'Statut', 'Priorité', 'Type', 'Cause', 'Début', 'Fin', 'Durée (min)', 'Opérateur']);
            foreach ($rows as $inc) {
                fputcsv($out, [
                    $inc->code_incident,
                    $inc->titre,
                    optional($inc->departement)->nom,
                    optional($inc->statut)->libelle,
                    optional($inc->priorite)->libelle,
                    optional($inc->typeIncident)->libelle,
                    optional($inc->cause)->libelle,
                    optional($inc->date_debut)?->format('Y-m-d H:i'),
                    optional($inc->date_fin)?->format('Y-m-d H:i'),
                    $inc->duree_minutes,
                    optional($inc->operateur)->name,
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'incidents.csv', $headers);
    }

    public function create(): View
    {
        return view('incidents.create', [
            'departements' => Departement::orderBy('nom')->get(),
            'statuts'      => Statut::orderBy('ordre')->get(),
            'priorites'    => Priorite::orderBy('niveau')->get(),
            'types'        => TypeIncident::orderBy('libelle')->get(),
            'causes'       => Cause::orderBy('libelle')->get(),
            'users'        => cache()->remember('users_for_incidents', 300, fn () => \App\Models\User::orderBy('name')->get()),
        ]);
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code_incident'] = $this->generateCode();
        $data['operateur_id'] = $request->user()->id;

        $incident = Incident::create($data);
        $this->syncDurationOnClosure($incident);

        $this->logAction($incident, 'create', 'Création de l’incident', [], $incident->only($incident->getFillable()));
        $this->logAudit($incident, 'create', ['message' => 'Incident créé']);

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident créé avec succès.');
    }

    public function show(Incident $incident): View
    {
        $incident->load([
            'departement',
            'typeIncident',
            'cause',
            'statut',
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
            'incident'     => $incident,
            'departements' => Departement::orderBy('nom')->get(),
            'statuts'      => Statut::orderBy('ordre')->get(),
            'priorites'    => Priorite::orderBy('niveau')->get(),
            'types'        => TypeIncident::orderBy('libelle')->get(),
            'causes'       => Cause::orderBy('libelle')->get(),
            'users'        => cache()->remember('users_for_incidents', 300, fn () => \App\Models\User::orderBy('name')->get()),
        ]);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $old = $incident->only($incident->getFillable());

        $incident->fill($request->validated());
        $incident->save();

        $this->syncDurationOnClosure($incident);

        $this->logAction($incident, 'update', 'Mise à jour de l’incident', $old, $incident->only($incident->getFillable()));
        $this->logAudit($incident, 'update', ['message' => 'Incident mis à jour']);

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident mis à jour avec succès.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $incident->delete();

        $this->logAction($incident, 'delete', 'Suppression de l’incident');
        $this->logAudit($incident, 'delete', ['message' => 'Incident supprimé']);

        return redirect()->route('incidents.index')->with('success', 'Incident supprimé.');
    }

    // ---------- Helpers ----------

    private function generateCode(): string
    {
        return 'INC-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
    }

    private function syncDurationOnClosure(Incident $incident): void
    {
        $incident->loadMissing('statut');

        $shouldClose = $incident->statut?->is_final;

        if ($shouldClose && is_null($incident->date_fin)) {
            $incident->date_fin = now();
        }

        if ($incident->date_fin) {
            $incident->duree_minutes = $incident->date_debut
                ? $incident->date_debut->diffInMinutes($incident->date_fin)
                : null;
            $incident->clotured_at = $incident->date_fin;
        }

        $incident->save();
    }

    private function logAction(Incident $incident, string $type, string $description, array $old = null, array $new = null): void
    {
        IncidentAction::create([
            'incident_id' => $incident->id,
            'user_id'     => auth()->id(),
            'action_type' => $type,
            'description' => $description,
            'action_date' => now(),
            'old_values'  => $old,
            'new_values'  => $new,
        ]);
    }

    private function logAudit(Incident $incident, string $action, array $details = []): void
    {
        Log::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'module'      => 'incidents',
            'target_type' => Incident::class,
            'target_id'   => $incident->id,
            'incident_id' => $incident->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'details'     => $details,
        ]);
    }
}
