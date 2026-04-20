<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cause extends Model
{
    use HasFactory;

    protected $table = 'causes';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'is_active',
        'type_incident_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function typeIncident()
    {
        return $this->belongsTo(TypeIncident::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
