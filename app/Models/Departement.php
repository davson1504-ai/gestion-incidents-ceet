<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $table = 'departements';

    protected $fillable = [
        'code',
        'nom',
        'zone',
        'direction_exploitation',
        'poste_repartition',
        'poste_source',
        'transformateur',
        'arrivee',
        'charge_maximale',
        'charge_unite',
        'description',
        'is_active',
    ];

    protected $casts = [
        'charge_maximale' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::saved(fn() => \App\Services\IncidentCatalogueService::invalidateCache());
        static::deleted(fn() => \App\Services\IncidentCatalogueService::invalidateCache());
    }
}
