<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentWitness extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'witness_name',
        'witness_phone',
        'witness_email',
        'witness_address',
        'statement',
        'statement_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'statement_date' => 'datetime',
        'status' => 'boolean'
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
} 