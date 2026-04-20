<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'is_active' => (bool) $this->is_active,
            'departement_id' => $this->departement_id,
            'departement' => DepartementResource::make($this->whenLoaded('departement')),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
