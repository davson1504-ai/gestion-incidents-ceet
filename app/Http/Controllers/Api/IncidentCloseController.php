<?php

namespace App\Http\Controllers\Api;

use App\Events\IncidentChanged;
use App\Http\Requests\Api\CloseIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class IncidentCloseController extends ApiController
{
    public function __construct(private readonly IncidentService $incidentService) {}

    public function store(CloseIncidentRequest $request, Incident $incident): JsonResponse
    {
        $closed = $this->incidentService->closeIncident($incident, $request->validated(), $request->user());
        $closed->load(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'operateur', 'responsable', 'superviseur']);

        broadcast(new IncidentChanged('closed', $closed))->toOthers();

        return $this->success(IncidentResource::make($closed), 'Incident cloture avec succes.');
    }
}
