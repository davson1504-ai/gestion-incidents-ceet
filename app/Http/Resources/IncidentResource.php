<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code_incident' => $this->code_incident,
            'titre' => $this->titre,
            'description' => $this->description,
            'departement_id' => $this->departement_id,
            'type_incident_id' => $this->type_incident_id,
            'cause_id' => $this->cause_id,
            'status_id' => $this->status_id,
            'priorite_id' => $this->priorite_id,
            'localisation' => $this->localisation,
            'date_debut' => $this->date_debut?->toIso8601String(),
            'date_fin' => $this->date_fin?->toIso8601String(),
            'duree_minutes' => $this->duree_minutes,
            'operateur_id' => $this->operateur_id,
            'responsable_id' => $this->responsable_id,
            'superviseur_id' => $this->superviseur_id,
            'actions_menees' => $this->actions_menees,
            'resolution_summary' => $this->resolution_summary,
            'resume_resolution' => $this->resolution_summary,
            'clotured_at' => $this->clotured_at?->toIso8601String(),
            'departement' => DepartementResource::make($this->whenLoaded('departement')),
            'type_incident' => TypeIncidentResource::make($this->whenLoaded('typeIncident')),
            'cause' => CauseResource::make($this->whenLoaded('cause')),
            'statut' => $this->whenLoaded('status', fn () => [
                'id' => $this->status?->id,
                'code' => $this->status?->code,
                'libelle' => $this->status?->libelle,
                'is_final' => (bool) ($this->status?->is_final ?? false),
            ]),
            'priorite' => $this->whenLoaded('priorite', fn () => [
                'id' => $this->priorite?->id,
                'code' => $this->priorite?->code,
                'libelle' => $this->priorite?->libelle,
                'niveau' => $this->priorite?->niveau,
            ]),
            'operateur' => UserResource::make($this->whenLoaded('operateur')),
            'responsable' => UserResource::make($this->whenLoaded('responsable')),
            'superviseur' => UserResource::make($this->whenLoaded('superviseur')),
            'interventions' => InterventionResource::collection($this->whenLoaded('interventions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
