<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour la table 'logs' d'audit de l'application.
 *
 * ✅ CORRECTION #3: Le nom "Log" est en conflit avec la Facade Laravel \Log
 * Solution : dans les fichiers qui utilisent ce modèle ET la facade Log,
 * il faut soit utiliser le FQCN complet, soit aliaser l'import.
 *
 * Dans IncidentController.php, on utilise:
 *   use App\Models\Log;
 * Et pour la facade (si besoin): \Illuminate\Support\Facades\Log::info(...)
 */
class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'target_type',
        'target_id',
        'incident_id',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}