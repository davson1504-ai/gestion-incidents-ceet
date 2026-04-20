<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CauseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'type_incident_id' => $this->type_incident_id,
            'type_incident' => TypeIncidentResource::make($this->whenLoaded('typeIncident')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
