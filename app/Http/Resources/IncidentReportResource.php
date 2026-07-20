<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'incident_id' => $this->incident_id,
            'user_id' => $this->user_id,
            'operateur_id' => $this->operateur_id,
            'actions_realisees' => $this->actions_realisees,
            'resultat' => $this->resultat,
            'observations' => $this->observations,
            'statut_rapport' => $this->statut_rapport,
            'motif_refus' => $this->motif_refus,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'date_soumission' => $this->date_soumission?->toIso8601String(),
            'date_validation' => $this->date_validation?->toIso8601String(),
            'date_refus' => $this->date_refus?->toIso8601String(),
            'valide_par' => $this->valide_par,
            'refuse_par' => $this->refuse_par,
            'operateur' => UserResource::make($this->whenLoaded('operateur')),
            'validateur' => UserResource::make($this->whenLoaded('validator')),
            'refuseur' => UserResource::make($this->whenLoaded('refuser')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
