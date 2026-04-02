<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeIncident extends Model
{
    use HasFactory;

    // Important : on force le nom de la table
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

    // Relations
    public function causes()
    {
        return $this->hasMany(Cause::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}