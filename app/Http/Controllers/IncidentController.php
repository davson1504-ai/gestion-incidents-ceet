<?php

namespace App\Http\Controllers;

use App\Events\IncidentChanged;
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
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:incidents.view')->only(['index', 'show', 'export']);
        $this->middleware('permission:incidents.create')->only(['create', 'store']);
        $this->middleware('permission:incidents.update')->only(['edit', 'update']);
        $this->middleware('permission:incidents.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $filters = array_merge(
            [
                'departement_id'   => null,
                'status_id'        => null,
                'priorite_id'      => null,
                'type_incident_id' => null,
                'cause_id'         => null,
                'operateur_id'     => null,
                'date_from'        => null,
                'date_to'          => null,
                'q'                => null,
            ],
            $request->only([
                'departement_id', 'status_id', 'priorite_id',
                'type_incident_id', 'cause_id', 'operateur_id',
                'date_from', 'date_to', 'q',
            ])
        );

        $baseQuery = Incident::query()
            ->when($filters['departement_id'],   fn ($q, $v) => $q->where('departement_id', $v))
            ->when($filters['status_id'],         fn ($q, $v) => $q->where('status_id', $v))
            ->when($filters['priorite_id'],       fn ($q, $v) => $q->where('priorite_id', $v))
            ->when($filters['type_incident_id'],  fn ($q, $v) => $q->where('type_incident_id', $v))
            ->when($filters['cause_id'],          fn ($q, $v) => $q->where('cause_id', $v))
            ->when($filters['operateur_id'],      fn ($q, $v) => $q->where('operateur_id', $v))
            ->when($filters['date_from'],         fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'],           fn ($q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->when($filters['q'], function ($q, $v) {
                $q->where(function ($sq) use ($v) {
                    $sq->where('code_incident', 'like', "%{$v}%")
                       ->orWhere('titre', 'like', "%{$v}%");
                });
            });

        $incidents = (clone $baseQuery)
            ->with(['departement', 'typeIncident', 'cause', 'statut', 'priorite', 'operateur', 'superviseur'])
            ->latest('date_debut')
            ->paginate(15)
            ->withQueryString();

        $statRows = (clone $baseQuery)
            ->selectRaw('status_id, priorite_id, duree_minutes')
            ->get();

        $allStatuts   = Statut::all()->keyBy('id');
        $allPriorites = Priorite::all()->keyBy('id');

        $byStatus = $statRows->groupBy('status_id')->map(function ($g, $statusId) use ($allStatuts) {
            $s = $allStatuts->get($statusId);
            return ['label' => $s?->libelle ?? 'Inconnu', 'color' => $s?->couleur ?? '#6c757d', 'total' => $g->count()];
        })->values();

        $byPriorite = $statRows->groupBy('priorite_id')->map(function ($g, $pId) use ($allPriorites) {
            $p = $allPriorites->get($pId);
            return ['label' => $p?->libelle ?? 'Inconnu', 'color' => $p?->couleur ?? '#6c757d', 'total' => $g->count()];
        })->values();

        $avgDuration = $statRows->whereNotNull('duree_minutes')->avg('duree_minutes');

        $openIds     = $allStatuts->where('is_final', false)->pluck('id');
        $closedIds   = $allStatuts->where('is_final', true)->pluck('id');
        $openCount   = $statRows->whereIn('status_id', $openIds)->count();
        $closedCount = $statRows->whereIn('status_id', $closedIds)->count();

        return view('incidents.index', [
            'incidents'    => $incidents,
            'departements' => Departement::orderBy('nom')->get(),
            'statuts'      => Statut::orderBy('ordre')->get(),
            'priorites'    => Priorite::orderBy('niveau')->get(),
            'types'        => TypeIncident::orderBy('libelle')->get(),
            'causes'       => Cause::orderBy('libelle')->get(),
            'operateurs'   => User::active()->orderBy('name')->get(),
            'filters'      => $filters,
            'stats'        => [
                'byStatus'    => $byStatus,
                'byPriorite'  => $byPriorite,
                'avgDuration' => $avgDuration,
                'openCount'   => $openCount,
                'closedCount' => $closedCount,
            ],
        ]);
    }

    public function export(Request $request)
    {
        // ✅ CORRECTION #2: Utiliser can() au lieu de authorize() pour les permissions Spatie
        if (! $request->user()->can('incidents.view')) {
            abort(403);
        }

        $filters = $request->only([
            'departement_id', 'status_id', 'priorite_id',
            'type_incident_id', 'cause_id', 'operateur_id',
            'date_from', 'date_to', 'q',
        ]);

        $rows = Incident::with(['departement', 'typeIncident', 'cause', 'statut', 'priorite', 'operateur'])
            ->when($filters['departement_id']   ?? null, fn ($q, $v) => $q->where('departement_id', $v))
            ->when($filters['status_id']        ?? null, fn ($q, $v) => $q->where('status_id', $v))
            ->when($filters['priorite_id']      ?? null, fn ($q, $v) => $q->where('priorite_id', $v))
            ->when($filters['type_incident_id'] ?? null, fn ($q, $v) => $q->where('type_incident_id', $v))
            ->when($filters['cause_id']         ?? null, fn ($q, $v) => $q->where('cause_id', $v))
            ->when($filters['operateur_id']     ?? null, fn ($q, $v) => $q->where('operateur_id', $v))
            ->when($filters['date_from']        ?? null, fn ($q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to']          ?? null, fn ($q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->when($filters['q']                ?? null, function ($q, $v) {
                $q->where(fn ($sq) => $sq->where('code_incident', 'like', "%{$v}%")->orWhere('titre', 'like', "%{$v}%"));
            })
            ->orderByDesc('date_debut')
            ->get();

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Code', 'Titre', 'Département', 'Statut', 'Priorité', 'Type', 'Cause', 'Début', 'Fin', 'Durée (min)', 'Opérateur'], ';');
            foreach ($rows as $inc) {
                fputcsv($out, [
                    $inc->code_incident,
                    $inc->titre,
                    optional($inc->departement)->nom,
                    optional($inc->statut)->libelle,
                    optional($inc->priorite)->libelle,
                    optional($inc->typeIncident)->libelle,
                    optional($inc->cause)->libelle,
                    optional($inc->date_debut)?->format('d/m/Y H:i'),
                    optional($inc->date_fin)?->format('d/m/Y H:i'),
                    $inc->duree_minutes,
                    optional($inc->operateur)->name,
                ], ';');
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'incidents-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('incidents.create', [
            'departements' => Departement::where('is_active', true)->orderBy('nom')->get(),
            'statuts'      => Statut::where('is_active', true)->orderBy('ordre')->get(),
            'priorites'    => Priorite::where('is_active', true)->orderBy('niveau')->get(),
            'types'        => TypeIncident::where('is_active', true)->orderBy('libelle')->get(),
            'causes'       => Cause::where('is_active', true)->orderBy('libelle')->get(),
            'users'        => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code_incident'] = $this->generateCode();
        $data['operateur_id']  = $request->user()->id;

        $incident = Incident::create($data);
        $this->syncDurationOnClosure($incident);

        $this->logAction($incident, 'create', "Création de l'incident", [], $incident->only($incident->getFillable()));
        $this->logAudit($incident, 'create', ['message' => 'Incident créé']);
        broadcast(new IncidentChanged('created', $incident))->toOthers();

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident créé avec succès.');
    }

    public function show(Incident $incident): View
    {
        $incident->load([
            'departement', 'typeIncident', 'cause', 'statut',
            'priorite', 'operateur', 'responsable', 'superviseur',
            'actions.user',
        ]);

        return view('incidents.show', compact('incident'));
    }

    public function edit(Incident $incident): View
    {
        return view('incidents.edit', [
            'incident'     => $incident,
            'departements' => Departement::where('is_active', true)->orderBy('nom')->get(),
            'statuts'      => Statut::where('is_active', true)->orderBy('ordre')->get(),
            'priorites'    => Priorite::where('is_active', true)->orderBy('niveau')->get(),
            'types'        => TypeIncident::where('is_active', true)->orderBy('libelle')->get(),
            'causes'       => Cause::where('is_active', true)->orderBy('libelle')->get(),
            'users'        => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $old = $incident->only($incident->getFillable());

        $incident->fill($request->validated());
        $incident->save();
        $this->syncDurationOnClosure($incident);

        $this->logAction($incident, 'update', "Mise à jour de l'incident", $old, $incident->only($incident->getFillable()));
        $this->logAudit($incident, 'update', ['message' => 'Incident mis à jour']);
        broadcast(new IncidentChanged('updated', $incident))->toOthers();

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident mis à jour avec succès.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $this->logAction($incident, 'delete', "Suppression de l'incident");
        $this->logAudit($incident, 'delete', ['message' => 'Incident supprimé']);
        broadcast(new IncidentChanged('deleted', $incident))->toOthers();

        $incident->delete();

        return redirect()->route('incidents.index')->with('success', 'Incident supprimé.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function generateCode(): string
    {
        return 'INC-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
    }

    private function syncDurationOnClosure(Incident $incident): void
    {
        $incident->loadMissing('statut');

        if ($incident->statut?->is_final && is_null($incident->date_fin)) {
            $incident->date_fin = now();
        }

        if ($incident->date_fin) {
            $incident->duree_minutes = $incident->date_debut
                ? $incident->date_debut->diffInMinutes($incident->date_fin)
                : null;
            $incident->clotured_at = $incident->date_fin;
            $incident->save();
        }
    }

    private function logAction(Incident $incident, string $type, string $description, ?array $old = null, ?array $new = null): void
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