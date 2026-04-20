<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\IncidentFilterRequest;
use App\Http\Requests\Api\StoreIncidentRequest;
use App\Http\Requests\Api\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Models\Statut;
use App\Services\IncidentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class IncidentController extends ApiController
{
    public function __construct(private readonly IncidentService $incidentService)
    {
        $this->authorizeResource(Incident::class, 'incident');
    }

    public function index(IncidentFilterRequest $request)
    {
        $filters = $request->validated();

        if (! empty($filters['statut']) && empty($filters['status_id'])) {
            $filters['status_id'] = Statut::query()->where('code', $filters['statut'])->value('id');
        }

        $query = Incident::query()
            ->with([
                'departement',
                'typeIncident',
                'cause',
                'status',
                'priorite',
                'operateur.roles',
                'responsable.roles',
                'superviseur.roles',
            ])
            ->filter($filters);

        if ($request->user()->isOperateur() && ! $request->user()->isSuperviseur() && ! $request->user()->isAdmin()) {
            $query->where(function (Builder $sub) use ($request) {
                $sub->where('operateur_id', $request->user()->id)
                    ->orWhere('responsable_id', $request->user()->id)
                    ->orWhere('superviseur_id', $request->user()->id);
            });
        }

        $sortBy = $filters['sort_by'] ?? 'date_debut';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $perPage = (int) ($filters['per_page'] ?? 15);

        $incidents = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return IncidentResource::collection($incidents);
    }

    public function store(StoreIncidentRequest $request): JsonResponse
    {
        $incident = $this->incidentService->createIncident($request->validated(), $request->user());
        $incident->load(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'responsable', 'superviseur']);

        return $this->success(IncidentResource::make($incident), 'Incident cree avec succes.', 201);
    }

    public function show(Incident $incident): JsonResponse
    {
        $incident->load([
            'departement',
            'typeIncident',
            'cause',
            'status',
            'priorite',
            'operateur.roles',
            'responsable.roles',
            'superviseur.roles',
            'interventions.user.roles',
        ]);

        return $this->success(IncidentResource::make($incident));
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): JsonResponse
    {
        $incident = $this->incidentService->updateIncident($incident, $request->validated(), $request->user());
        $incident->load(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'responsable', 'superviseur']);

        return $this->success(IncidentResource::make($incident), 'Incident mis a jour avec succes.');
    }

    public function destroy(Incident $incident): JsonResponse
    {
        $userId = (int) auth()->id();
        $this->incidentService->logAction($incident, $userId, 'delete', "Suppression de l'incident");
        $this->incidentService->logAudit($incident, $userId, 'delete', ['message' => 'Incident supprime']);

        $incident->delete();

        return $this->success(null, 'Incident supprime avec succes.');
    }
}
