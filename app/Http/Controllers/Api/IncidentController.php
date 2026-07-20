<?php

namespace App\Http\Controllers\Api;

use App\Events\IncidentChanged;
use App\Http\Requests\Api\IncidentFilterRequest;
use App\Http\Requests\Api\StoreIncidentRequest;
use App\Http\Requests\Api\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Models\Statut;
use App\Services\IncidentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                'report.operateur.roles',
                'report.validator.roles',
                'report.refuser.roles',
            ])
            ->visibleToUser($request->user())
            ->filter($filters);

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
        $incident->load(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'responsable', 'superviseur', 'report']);

        broadcast(new IncidentChanged('created', $incident))->toOthers();

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
            'report.operateur.roles',
            'report.validator.roles',
            'report.refuser.roles',
        ]);

        return $this->success(IncidentResource::make($incident));
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): JsonResponse
    {
        $incident = $this->incidentService->updateIncident($incident, $request->validated(), $request->user());
        $incident->load(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'responsable', 'superviseur', 'report']);

        broadcast(new IncidentChanged('updated', $incident))->toOthers();

        return $this->success(IncidentResource::make($incident), 'Incident mis a jour avec succes.');
    }

    public function destroy(Request $request, Incident $incident): JsonResponse
    {
        broadcast(new IncidentChanged('deleted', $incident))->toOthers();

        $this->incidentService->deleteIncident($incident, $request->user());

        return $this->success(null, 'Incident supprime avec succes.');
    }
}
