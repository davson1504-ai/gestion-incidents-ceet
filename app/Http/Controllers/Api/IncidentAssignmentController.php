<?php

namespace App\Http\Controllers\Api;

use App\Events\IncidentChanged;
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

        broadcast(new IncidentChanged('assigned', $updated))->toOthers();

        return $this->success(IncidentResource::make($updated), 'Incident assigne avec succes.');
    }
}
