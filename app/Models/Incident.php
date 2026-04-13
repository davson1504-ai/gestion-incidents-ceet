<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function calculerDuree(): void
    {
        if ($this->date_fin) {
            $this->duree_minutes = $this->date_debut->diffInMinutes($this->date_fin);
            $this->save();
        }
    }
}