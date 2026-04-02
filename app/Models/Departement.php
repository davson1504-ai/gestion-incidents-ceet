<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'direction_exploitation',
        'poste_repartition',
        'transformateur',
        'arrivee',
        'charge_maximale',
        'charge_unite',
        'zone',
        'poste_source',
        'description',
        'is_active',
    ];

    protected $casts = [
        'charge_maximale' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    // Relations
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}