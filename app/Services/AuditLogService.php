<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Log;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function record(
        string $action,
        string $module,
        ?int $userId = null,
        ?Model $target = null,
        ?int $incidentId = null,
        array $details = []
    ): Log {
        return Log::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'incident_id' => $incidentId,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'details' => $details,
        ]);
    }

    public function recordIncident(
        string $action,
        Incident $incident,
        ?int $userId = null,
        array $details = []
    ): Log {
        return $this->record(
            action: $action,
            module: 'incidents',
            userId: $userId,
            target: $incident,
            incidentId: $incident->id,
            details: $details,
        );
    }
}
