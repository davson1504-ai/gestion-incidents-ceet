<?php

namespace App\Notifications;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncidentAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Incident $incident,
        private readonly User $assignedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->incident->loadMissing(['departement', 'status', 'priorite']);

        return [
            'kind' => 'incident_assigned',
            'title' => 'Incident affecté',
            'message' => "L'incident {$this->incident->code_incident} vous a été affecté.",
            'incident_id' => $this->incident->id,
            'incident_code' => $this->incident->code_incident,
            'incident_title' => $this->incident->titre,
            'incident_url' => route('incidents.show', $this->incident),
            'departement' => $this->incident->departement?->nom,
            'status' => $this->incident->status?->libelle,
            'priorite' => $this->incident->priorite?->libelle,
            'assigned_by_id' => $this->assignedBy->id,
            'assigned_by_name' => $this->assignedBy->name,
            'date_debut' => $this->incident->date_debut?->toIso8601String(),
        ];
    }
}
