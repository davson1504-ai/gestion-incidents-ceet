<?php

namespace App\Notifications;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncidentEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Incident $incident,
        private readonly string $kind,
        private readonly string $title,
        private readonly string $message,
        private readonly User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->incident->loadMissing(['departement', 'status', 'priorite']);

        return [
            'kind' => $this->kind,
            'title' => $this->title,
            'message' => $this->message,
            'incident_id' => $this->incident->id,
            'incident_code' => $this->incident->code_incident,
            'incident_title' => $this->incident->titre,
            'incident_url' => route('incidents.show', $this->incident),
            'departement' => $this->incident->departement?->nom,
            'status' => $this->incident->status?->libelle,
            'priorite' => $this->incident->priorite?->libelle,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'date_debut' => $this->incident->date_debut?->toIso8601String(),
        ];
    }
}
