<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentAction;
use App\Models\Intervention;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncidentService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function generateCode(): string
    {
        do {
            $code = 'INC-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (Incident::query()->where('code_incident', $code)->exists());

        return $code;
    }

    public function createIncident(array $payload, User $actor): Incident
    {
        return DB::transaction(function () use ($payload, $actor) {
            $payload['code_incident'] = $payload['code_incident'] ?? $this->generateCode();
            $payload['operateur_id'] = $payload['operateur_id'] ?? $actor->id;
            $payload['status_id'] = $payload['status_id'] ?? $this->defaultStatusId();
            $payload['priorite_id'] = $payload['priorite_id'] ?? $this->defaultPrioriteId();

            if (array_key_exists('resume_resolution', $payload) && ! array_key_exists('resolution_summary', $payload)) {
                $payload['resolution_summary'] = $payload['resume_resolution'];
            }

            $incident = Incident::create($payload);
            $this->syncDurationOnClosure($incident);

            $this->logAction(
                incident: $incident,
                userId: $actor->id,
                type: 'create',
                description: "Creation de l'incident",
                old: [],
                new: $incident->only($incident->getFillable())
            );

            $this->logAudit($incident, $actor->id, 'create', ['message' => 'Incident cree']);

            return $incident;
        });
    }

    public function updateIncident(Incident $incident, array $payload, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $oldValues = $incident->only($incident->getFillable());

            if (array_key_exists('resume_resolution', $payload) && ! array_key_exists('resolution_summary', $payload)) {
                $payload['resolution_summary'] = $payload['resume_resolution'];
            }

            $incident->fill($payload);
            $incident->save();

            $this->syncDurationOnClosure($incident);

            $this->logAction(
                incident: $incident,
                userId: $actor->id,
                type: 'update',
                description: "Mise a jour de l'incident",
                old: $oldValues,
                new: $incident->only($incident->getFillable())
            );

            $this->logAudit($incident, $actor->id, 'update', ['message' => 'Incident mis a jour']);

            return $incident->refresh();
        });
    }

    public function assignIncident(Incident $incident, array $payload, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $oldValues = $incident->only(['responsable_id', 'superviseur_id']);

            $incident->responsable_id = (int) $payload['responsable_id'];
            if (array_key_exists('superviseur_id', $payload)) {
                $incident->superviseur_id = $payload['superviseur_id'] ?: null;
            } elseif (! $incident->superviseur_id) {
                $incident->superviseur_id = $actor->id;
            }
            $incident->save();

            $this->logAction(
                incident: $incident,
                userId: $actor->id,
                type: 'assignation',
                description: $payload['commentaire'] ?? "Incident assigne a un responsable",
                old: $oldValues,
                new: $incident->only(['responsable_id', 'superviseur_id'])
            );

            $this->logAudit($incident, $actor->id, 'assign', [
                'responsable_id' => $incident->responsable_id,
                'superviseur_id' => $incident->superviseur_id,
            ]);

            return $incident->refresh();
        });
    }

    public function closeIncident(Incident $incident, array $payload, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $oldValues = $incident->only([
                'status_id',
                'date_fin',
                'duree_minutes',
                'actions_menees',
                'resolution_summary',
            ]);

            $incident->actions_menees = $payload['actions_menees'] ?? $incident->actions_menees;
            $incident->resolution_summary = $payload['resolution_summary']
                ?? $payload['resume_resolution']
                ?? $incident->resolution_summary;

            if (isset($payload['status_id'])) {
                $incident->status_id = (int) $payload['status_id'];
            } else {
                $incident->status_id = $this->defaultClosedStatusId();
            }

            $incident->date_fin = isset($payload['date_fin']) ? Carbon::parse($payload['date_fin']) : now();
            $incident->clotured_at = $incident->date_fin;
            $incident->recalculateDuration();
            $incident->save();

            $this->logAction(
                incident: $incident,
                userId: $actor->id,
                type: 'cloture',
                description: "Cloture de l'incident",
                old: $oldValues,
                new: $incident->only([
                    'status_id',
                    'date_fin',
                    'duree_minutes',
                    'actions_menees',
                    'resolution_summary',
                ])
            );

            $this->logAudit($incident, $actor->id, 'close', [
                'duree_minutes' => $incident->duree_minutes,
            ]);

            return $incident->refresh();
        });
    }

    public function addIntervention(Incident $incident, array $payload, User $actor): Intervention
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $startedAt = Carbon::parse($payload['started_at']);
            $endedAt = isset($payload['ended_at']) ? Carbon::parse($payload['ended_at']) : null;

            $intervention = Intervention::create([
                'incident_id' => $incident->id,
                'user_id' => $actor->id,
                'action_type' => $payload['action_type'],
                'description' => $payload['description'],
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duree_minutes' => $endedAt ? $startedAt->diffInMinutes($endedAt) : null,
                'resultat' => $payload['resultat'] ?? null,
                'statut' => $payload['statut'] ?? null,
            ]);

            $this->logAction(
                incident: $incident,
                userId: $actor->id,
                type: 'intervention',
                description: 'Ajout intervention terrain',
                old: null,
                new: $intervention->only($intervention->getFillable())
            );

            $this->logAudit($incident, $actor->id, 'intervention', [
                'intervention_id' => $intervention->id,
                'action_type' => $intervention->action_type,
            ]);

            return $intervention;
        });
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
        int $userId,
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
        $this->auditLogService->recordIncident($action, $incident, $userId, $details);
    }

    private function defaultStatusId(): int
    {
        return (int) (Statut::query()->where('code', 'EN_COURS')->value('id')
            ?? Statut::query()->orderBy('ordre')->value('id'));
    }

    private function defaultClosedStatusId(): int
    {
        return (int) (Statut::query()->where('code', 'CLOTURE')->value('id')
            ?? Statut::query()->where('is_final', true)->orderBy('ordre')->value('id')
            ?? $this->defaultStatusId());
    }

    private function defaultPrioriteId(): int
    {
        return (int) (Priorite::query()->where('code', 'MEDIUM')->value('id')
            ?? Priorite::query()->orderBy('niveau')->value('id'));
    }
}
