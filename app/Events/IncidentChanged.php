<?php

namespace App\Events;

use App\Models\Incident;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public Incident $incident,
    ) {
        $this->incident->loadMissing(['status', 'priorite']);
    }

    public function broadcastOn(): array
    {
        return [new Channel('incidents')];
    }

    public function broadcastAs(): string
    {
        return 'incident.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'id' => $this->incident->id,
            'code_incident' => $this->incident->code_incident,
            'status' => $this->incident->status?->libelle,
            'priorite' => $this->incident->priorite?->libelle,
            'updated_at' => optional($this->incident->updated_at)->toISOString(),
        ];
    }
}
