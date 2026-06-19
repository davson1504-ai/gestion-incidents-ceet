<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentAction;
use App\Models\IncidentReport;
use App\Models\Intervention;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\User;
use App\Notifications\IncidentAssignedNotification;
use App\Notifications\IncidentEventNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            $payload['status_id'] = ! empty($payload['responsable_id'])
                ? $this->statusId('AFFECTE')
                : $this->statusId('OUVERT');
            $payload['priorite_id'] = $payload['priorite_id'] ?? $this->defaultPrioriteId();

            if (array_key_exists('resume_resolution', $payload) && ! array_key_exists('resolution_summary', $payload)) {
                $payload['resolution_summary'] = $payload['resume_resolution'];
            }

            // CEET: creation must not persist operator treatment fields.
            // These fields are reserved for operator resolution/report workflow after assignment.
            unset(
                $payload['actions_menees'],
                $payload['resolution_summary'],
                $payload['resume_resolution'],
                $payload['actions_realisees'],
                $payload['resultat_obtenu']
            );

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

            $incident = $incident->refresh();

            if ($incident->responsable_id) {
                User::query()
                    ->find($incident->responsable_id)
                    ?->notify(new IncidentAssignedNotification($incident, $actor));
            } else {
                $this->notifyIncidentEvent(
                    incident: $incident,
                    kind: 'incident_created',
                    title: 'Nouvel incident',
                    message: "L'incident {$incident->code_incident} attend une affectation.",
                    actor: $actor,
                    recipients: $this->supervisorsForIncidentAssignment()
                );
            }

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

            $incident = $incident->refresh();

            $this->notifyIncidentEvent(
                incident: $incident,
                kind: 'incident_updated',
                title: 'Incident mis à jour',
                message: "L'incident {$incident->code_incident} a été mis à jour.",
                actor: $actor,
                recipients: $this->incidentParticipants($incident)
            );

            return $incident;
        });
    }

    public function assignIncident(Incident $incident, array $payload, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $oldValues = $incident->only(['responsable_id', 'superviseur_id', 'status_id']);
            $oldResponsableId = $incident->responsable_id;

            $incident->responsable_id = (int) $payload['responsable_id'];
            $incident->status_id = $this->statusId('AFFECTE');
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
                new: $incident->only(['responsable_id', 'superviseur_id', 'status_id'])
            );

            $this->logAudit($incident, $actor->id, 'assign', [
                'responsable_id' => $incident->responsable_id,
                'superviseur_id' => $incident->superviseur_id,
            ]);

            $incident = $incident->refresh();

            if ($incident->responsable_id && (int) $oldResponsableId !== (int) $incident->responsable_id) {
                User::query()
                    ->find($incident->responsable_id)
                    ?->notify(new IncidentAssignedNotification($incident, $actor));
            }

            $this->notifyIncidentEvent(
                incident: $incident,
                kind: 'incident_assigned_supervision',
                title: 'Incident affecté',
                message: "L'incident {$incident->code_incident} a été affecté.",
                actor: $actor,
                recipients: $this->incidentParticipants($incident)->reject(fn (User $user) => $user->id === $incident->responsable_id)
            );

            return $incident;
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

            $this->ensureStatus($incident, 'VALIDE', 'Seul un incident valide peut etre cloture.');
            $incident->loadMissing('report');
            if (! $incident->report) {
                throw ValidationException::withMessages([
                    'rapport' => 'La cloture est impossible sans rapport d intervention.',
                ]);
            }

            $incident->actions_menees = $payload['actions_menees'] ?? $incident->actions_menees;
            $incident->resolution_summary = $payload['resolution_summary']
                ?? $payload['resume_resolution']
                ?? $incident->resolution_summary;

            $incident->status_id = $this->statusId('CLOTURE');

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

            $incident = $incident->refresh();

            $this->notifyIncidentEvent(
                incident: $incident,
                kind: 'incident_closed',
                title: 'Incident clôturé',
                message: "L'incident {$incident->code_incident} a été clôturé.",
                actor: $actor,
                recipients: $this->incidentParticipants($incident)
            );

            return $incident;
        });
    }

    public function addIntervention(Incident $incident, array $payload, User $actor): Intervention
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $this->ensureStatus($incident, 'AFFECTE', 'Seul un incident affecte peut etre pris en charge.');

            $startedAt = Carbon::parse($payload['started_at']);
            $endedAt = isset($payload['ended_at']) ? Carbon::parse($payload['ended_at']) : null;

            $intervention = Intervention::updateOrCreate([
                'incident_id' => $incident->id,
            ], [
                'incident_id' => $incident->id,
                'user_id' => $actor->id,
                'action_type' => $payload['action_type'] ?? 'prise_en_charge',
                'description' => $payload['description'],
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duree_minutes' => $endedAt ? $startedAt->diffInMinutes($endedAt) : null,
                'resultat' => $payload['resultat'] ?? null,
                'statut' => $payload['statut'] ?? null,
            ]);

            $oldStatusId = $incident->status_id;
            $incident->status_id = $this->statusId('EN_COURS');
            $incident->save();

            $this->logAction(
                incident: $incident,
                userId: $actor->id,
                type: 'prise_en_charge',
                description: 'Prise en charge de l incident',
                old: ['status_id' => $oldStatusId],
                new: array_merge($intervention->only($intervention->getFillable()), ['status_id' => $incident->status_id])
            );

            $this->logAudit($incident, $actor->id, 'take', [
                'intervention_id' => $intervention->id,
                'action_type' => $intervention->action_type,
            ]);

            $incident = $incident->refresh();

            $this->notifyIncidentEvent(
                incident: $incident,
                kind: 'incident_taken',
                title: 'Incident pris en charge',
                message: "L'incident {$incident->code_incident} est en cours de traitement.",
                actor: $actor,
                recipients: $this->incidentParticipants($incident)
            );

            return $intervention;
        });
    }

    public function resolveIncident(Incident $incident, array $payload, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $this->ensureStatus($incident, 'EN_COURS', 'Seul un incident en cours peut etre marque comme resolu.');

            $oldValues = $incident->only(['status_id', 'actions_menees', 'resolution_summary']);
            $incident->actions_menees = $payload['actions_menees'] ?? $incident->actions_menees;
            $incident->resolution_summary = $payload['resolution_summary'] ?? $payload['resultat'] ?? $incident->resolution_summary;
            $incident->status_id = $this->statusId('RESOLU');
            $incident->save();

            $incident->intervention()->update([
                'ended_at' => isset($payload['ended_at']) ? Carbon::parse($payload['ended_at']) : now(),
                'resultat' => $payload['resultat'] ?? $incident->resolution_summary,
                'statut' => 'resolu',
            ]);

            if ($incident->intervention) {
                $startedAt = $incident->intervention->started_at;
                $endedAt = $incident->intervention->ended_at;
                $incident->intervention->forceFill([
                    'duree_minutes' => $startedAt && $endedAt ? $startedAt->diffInMinutes($endedAt) : null,
                ])->save();
            }

            $this->logAction($incident, $actor->id, 'resolution', 'Incident marque comme resolu', $oldValues, $incident->only(['status_id', 'actions_menees', 'resolution_summary']));
            $this->logAudit($incident, $actor->id, 'resolve', ['message' => 'Incident resolu']);

            $incident = $incident->refresh();

            $this->notifyIncidentEvent(
                incident: $incident,
                kind: 'incident_resolved',
                title: 'Incident résolu',
                message: "L'incident {$incident->code_incident} a été marqué comme résolu.",
                actor: $actor,
                recipients: $this->incidentParticipants($incident)
            );

            return $incident;
        });
    }

    public function submitReport(Incident $incident, array $payload, User $actor): IncidentReport
    {
        return DB::transaction(function () use ($incident, $payload, $actor) {
            $this->ensureStatus($incident, 'RESOLU', 'Le rapport ne peut etre redige qu apres resolution.');

            $report = IncidentReport::updateOrCreate([
                'incident_id' => $incident->id,
            ], [
                'user_id' => $actor->id,
                'actions_realisees' => $payload['actions_realisees'],
                'resultat' => $payload['resultat'],
                'observations' => $payload['observations'] ?? null,
                'submitted_at' => isset($payload['submitted_at']) ? Carbon::parse($payload['submitted_at']) : now(),
            ]);

            $oldStatusId = $incident->status_id;
            $incident->status_id = $this->statusId('RAPPORTE');
            $incident->save();

            $this->logAction($incident, $actor->id, 'rapport', 'Rapport d intervention redige', ['status_id' => $oldStatusId], ['status_id' => $incident->status_id, 'report_id' => $report->id]);
            $this->logAudit($incident, $actor->id, 'report', ['report_id' => $report->id]);

            $incident = $incident->refresh();

            $this->notifyIncidentEvent(
                incident: $incident,
                kind: 'incident_reported',
                title: 'Rapport soumis',
                message: "Le rapport de l'incident {$incident->code_incident} attend validation.",
                actor: $actor,
                recipients: $this->incidentParticipants($incident)
                    ->merge($this->supervisorsForIncidentValidation())
            );

            return $report;
        });
    }

    public function validateResolution(Incident $incident, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $actor) {
            $this->ensureStatus($incident, 'RAPPORTE', 'Seul un incident rapporte peut etre valide.');

            $oldStatusId = $incident->status_id;
            $incident->status_id = $this->statusId('VALIDE');
            $incident->save();

            $this->logAction($incident, $actor->id, 'validation', 'Resolution validee par le superviseur', ['status_id' => $oldStatusId], ['status_id' => $incident->status_id]);
            $this->logAudit($incident, $actor->id, 'validate', ['message' => 'Resolution validee']);

            $incident = $incident->refresh();

            $this->notifyIncidentEvent(
                incident: $incident,
                kind: 'incident_validated',
                title: 'Résolution validée',
                message: "La résolution de l'incident {$incident->code_incident} a été validée.",
                actor: $actor,
                recipients: $this->incidentParticipants($incident)
            );

            return $incident;
        });
    }

    public function deleteIncident(Incident $incident, User $actor): void
    {
        DB::transaction(function () use ($incident, $actor) {
            $this->logAction($incident, $actor->id, 'delete', "Suppression de l'incident");
            $this->logAudit($incident, $actor->id, 'delete', ['message' => 'Incident supprime']);
            $incident->delete();
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

    private function notifyIncidentEvent(
        Incident $incident,
        string $kind,
        string $title,
        string $message,
        User $actor,
        Collection $recipients,
    ): void {
        $recipients
            ->filter(fn (User $user) => $user->id !== $actor->id)
            ->unique('id')
            ->values()
            ->each(fn (User $user) => $user->notify(
                new IncidentEventNotification($incident, $kind, $title, $message, $actor)
            ));
    }

    private function incidentParticipants(Incident $incident): Collection
    {
        $ids = collect([
            $incident->responsable_id,
            $incident->superviseur_id,
            $incident->operateur_id,
        ])
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return User::query()
            ->active()
            ->whereIn('id', $ids)
            ->get();
    }

    private function supervisorsForIncidentAssignment(): Collection
    {
        return User::query()
            ->active()
            ->permission('incidents.assign')
            ->get();
    }

    private function supervisorsForIncidentValidation(): Collection
    {
        return User::query()
            ->active()
            ->permission('incidents.validate')
            ->get();
    }

    private function defaultStatusId(): int
    {
        return (int) (Statut::query()->where('code', 'OUVERT')->value('id')
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

    private function statusId(string $code): int
    {
        return (int) (Statut::query()->where('code', $code)->value('id') ?? $this->defaultStatusId());
    }

    private function ensureStatus(Incident $incident, string $code, string $message): void
    {
        $incident->loadMissing('status');

        if ($incident->status?->code !== $code) {
            throw ValidationException::withMessages([
                'status' => $message,
            ]);
        }
    }
}
