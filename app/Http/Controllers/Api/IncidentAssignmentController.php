<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AssignIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class IncidentAssignmentController extends ApiController
{
    public function __construct(private readonly IncidentService $incidentService) {}

    public function store(AssignIncidentRequest $request, Incident $incident): JsonResponse
    {
        $updated = $this->incidentService->assignIncident($incident, $request->validated(), $request->user());
        $updated->load(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'responsable', 'superviseur']);

        return $this->success(IncidentResource::make($updated), 'Incident assigne avec succes.');
    }
}
