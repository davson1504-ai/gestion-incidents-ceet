<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'user_id',
        'action_type',
        'description',
        'action_date',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'action_date' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relations
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
