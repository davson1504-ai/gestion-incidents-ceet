<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priorite extends Model
{
    use HasFactory;

    protected $table = 'priorites';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'niveau',
        'couleur',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'priorite_id');
    }
}
