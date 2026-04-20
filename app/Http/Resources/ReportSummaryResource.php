<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_incidents' => (int) ($this['total_incidents'] ?? 0),
            'duree_moyenne_minutes' => (float) ($this['duree_moyenne_minutes'] ?? 0),
            'incidents_ouverts' => (int) ($this['incidents_ouverts'] ?? 0),
            'incidents_fermes' => (int) ($this['incidents_fermes'] ?? 0),
        ];
    }
}
