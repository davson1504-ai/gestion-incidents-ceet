<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_incident',
        'titre',
        'description',
        'departement_id',
        'type_incident_id',
        'cause_id',
        'status_id',
        'priorite_id',
        'localisation',
        'date_debut',
        'date_fin',
        'duree_minutes',
        'operateur_id',
        'responsable_id',
        'superviseur_id',
        'actions_menees',
        'resolution_summary',
        'clotured_at',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'clotured_at' => 'datetime',
        'duree_minutes' => 'integer',
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function typeIncident()
    {
        return $this->belongsTo(TypeIncident::class);
    }

    public function cause()
    {
        return $this->belongsTo(Cause::class);
    }

    public function status()
    {
        return $this->belongsTo(Statut::class, 'status_id');
    }

    public function statut()
    {
        return $this->status();
    }

    public function priorite()
    {
        return $this->belongsTo(Priorite::class);
    }

    public function operateur()
    {
        return $this->belongsTo(User::class, 'operateur_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function superviseur()
    {
        return $this->belongsTo(User::class, 'superviseur_id');
    }

    public function actions()
    {
        return $this->hasMany(IncidentAction::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function getStatutAttribute(): ?Statut
    {
        if ($this->relationLoaded('status')) {
            return $this->getRelation('status');
        }

        return $this->status()->getResults();
    }

    public function getResumeResolutionAttribute(): ?string
    {
        return $this->resolution_summary;
    }

    public function setResumeResolutionAttribute(?string $value): void
    {
        $this->attributes['resolution_summary'] = $value;
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['departement_id'] ?? null, fn (Builder $q, $v) => $q->where('departement_id', $v))
            ->when($filters['type_incident_id'] ?? null, fn (Builder $q, $v) => $q->where('type_incident_id', $v))
            ->when($filters['cause_id'] ?? null, fn (Builder $q, $v) => $q->where('cause_id', $v))
            ->when($filters['operateur_id'] ?? null, fn (Builder $q, $v) => $q->where('operateur_id', $v))
            ->when($filters['status_id'] ?? null, fn (Builder $q, $v) => $q->where('status_id', $v))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $v) => $q->whereDate('date_debut', '>=', $v))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $v) => $q->whereDate('date_debut', '<=', $v))
            ->when($filters['q'] ?? null, function (Builder $q, $term) {
                $term = trim((string) $term);
                if ($term === '') {
                    return;
                }

                $q->where(function (Builder $search) use ($term) {
                    $search->where('code_incident', 'like', "%{$term}%")
                        ->orWhere('titre', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('localisation', 'like', "%{$term}%")
                        ->orWhere('resolution_summary', 'like', "%{$term}%");
                });
            });
    }

    public function recalculateDuration(): void
    {
        if (! $this->date_debut || ! $this->date_fin) {
            return;
        }

        $this->duree_minutes = $this->date_debut->diffInMinutes($this->date_fin);
        $this->clotured_at = $this->clotured_at ?? $this->date_fin;
    }

    public function close(?Carbon $closedAt = null): void
    {
        $closedAt ??= now();
        $this->date_fin = $this->date_fin ?? $closedAt;
        $this->clotured_at = $closedAt;
        $this->recalculateDuration();
    }

    public function calculerDuree(): void
    {
        $this->recalculateDuration();
        $this->save();
    }
}
