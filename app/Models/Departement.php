<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $table = 'departements';

    // ✅ CORRECTION #4: Fillable complet incluant les champs ajoutés
    // via la migration 2026_03_30_202116_add_ceet_fields_to_departements_table
    protected $fillable = [
        'code',
        'nom',
        'zone',
        'direction_exploitation',   // ← ajouté par migration CEET
        'poste_repartition',        // ← ajouté par migration CEET
        'poste_source',
        'transformateur',           // ← ajouté par migration CEET
        'arrivee',                  // ← ajouté par migration CEET
        'charge_maximale',          // ← ajouté par migration CEET
        'charge_unite',             // ← ajouté par migration CEET
        'description',
        'is_active',
    ];

    protected $casts = [
        'charge_maximale' => 'decimal:2',
        'is_active' => 'boolean',
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
