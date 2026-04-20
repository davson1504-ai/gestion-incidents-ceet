<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'departement_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function incidentsDeclares()
    {
        return $this->hasMany(Incident::class, 'operateur_id');
    }

    public function incidentsAssignes()
    {
        return $this->hasMany(Incident::class, 'responsable_id');
    }

    public function incidentsSupervises()
    {
        return $this->hasMany(Incident::class, 'superviseur_id');
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOperateurs(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $roleQuery) {
            $roleQuery->whereIn('name', ['Operateur', 'Opérateur', 'operateur']);
        });
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['Administrateur', 'admin']);
    }

    public function isSuperviseur(): bool
    {
        return $this->hasAnyRole(['Superviseur', 'superviseur']);
    }

    public function isOperateur(): bool
    {
        return $this->hasAnyRole(['Operateur', 'Opérateur', 'operateur']);
    }
}
