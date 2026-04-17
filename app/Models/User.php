<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * Spatie guard name to keep roles/permissions on the web guard.
     */
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

    // ====================== RELATIONS ======================

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function incidentsDeclares()
    {
        return $this->hasMany(Incident::class, 'operateur_id');
    }

    public function incidentsSupervises()
    {
        return $this->hasMany(Incident::class, 'superviseur_id');
    }

    public function actions()
    {
        return $this->hasMany(IncidentAction::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
