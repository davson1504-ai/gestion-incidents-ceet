<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'nom' => $this->nom,
            'zone' => $this->zone,
            'direction_exploitation' => $this->direction_exploitation,
            'poste_repartition' => $this->poste_repartition,
            'poste_source' => $this->poste_source,
            'transformateur' => $this->transformateur,
            'arrivee' => $this->arrivee,
            'charge_maximale' => $this->charge_maximale,
            'charge_unite' => $this->charge_unite,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
