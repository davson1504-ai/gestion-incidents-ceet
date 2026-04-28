<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeIncident extends Model
{
    use HasFactory;

    protected $table = 'type_incidents';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function causes()
    {
        return $this->hasMany(Cause::class);
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
