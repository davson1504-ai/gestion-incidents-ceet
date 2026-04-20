<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreInterventionRequest;
use App\Http\Resources\InterventionResource;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class IncidentInterventionController extends ApiController
{
    public function __construct(private readonly IncidentService $incidentService) {}

    public function store(StoreInterventionRequest $request, Incident $incident): JsonResponse
    {
        $intervention = $this->incidentService->addIntervention($incident, $request->validated(), $request->user());
        $intervention->load('user.roles');

        return $this->success(InterventionResource::make($intervention), 'Intervention enregistree avec succes.', 201);
    }
}
