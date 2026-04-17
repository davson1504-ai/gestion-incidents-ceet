<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentAction;
use App\Models\Log as IncidentLog;
use Illuminate\Support\Str;

class IncidentService
{
    public function generateCode(): string
    {
        do {
            $code = 'INC-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (Incident::where('code_incident', $code)->exists());

        return $code;
    }

    public function syncDurationOnClosure(Incident $incident): void
    {
        $incident->loadMissing('status');

        if ($incident->status?->is_final && is_null($incident->date_fin)) {
            $incident->date_fin = now();
        }

        if ($incident->date_fin) {
            $incident->duree_minutes = $incident->date_debut
                ? $incident->date_debut->diffInMinutes($incident->date_fin)
                : null;
            $incident->clotured_at = $incident->date_fin;
            $incident->save();
        }
    }

    public function logAction(
        Incident $incident,
        ?int $userId,
        string $type,
        string $description,
        ?array $old = null,
        ?array $new = null,
    ): void {
        IncidentAction::create([
            'incident_id' => $incident->id,
            'user_id' => $userId,
            'action_type' => $type,
            'description' => $description,
            'action_date' => now(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    public function logAudit(Incident $incident, ?int $userId, string $action, array $details = []): void
    {
        IncidentLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => 'incidents',
            'target_type' => Incident::class,
            'target_id' => $incident->id,
            'incident_id' => $incident->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
        ]);
    }
}
