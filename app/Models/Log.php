<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

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
        'details'     => 'array',
        'created_at'  => 'datetime',
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