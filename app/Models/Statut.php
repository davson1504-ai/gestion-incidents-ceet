<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statut extends Model
{
    use HasFactory;

    // Important : on force le nom de la table
    protected $table = 'statuses';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'ordre',
        'couleur',
        'is_active',
        'is_final',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_final' => 'boolean',
    ];

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'status_id');
    }
}
