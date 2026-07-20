<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'BROUILLON';
    public const STATUS_SUBMITTED = 'SOUMIS';
    public const STATUS_VALIDATED = 'VALIDE';
    public const STATUS_REJECTED = 'REFUSE';

    protected $fillable = [
        'incident_id',
        'user_id',
        'operateur_id',
        'actions_realisees',
        'resultat',
        'observations',
        'statut_rapport',
        'motif_refus',
        'submitted_at',
        'date_soumission',
        'date_validation',
        'date_refus',
        'valide_par',
        'refuse_par',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'date_soumission' => 'datetime',
        'date_validation' => 'datetime',
        'date_refus' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function operateur()
    {
        return $this->belongsTo(User::class, 'operateur_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function refuser()
    {
        return $this->belongsTo(User::class, 'refuse_par');
    }

    public function isDraft(): bool
    {
        return $this->statut_rapport === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->statut_rapport === self::STATUS_SUBMITTED;
    }

    public function isValidated(): bool
    {
        return $this->statut_rapport === self::STATUS_VALIDATED;
    }

    public function isRejected(): bool
    {
        return $this->statut_rapport === self::STATUS_REJECTED;
    }
}
