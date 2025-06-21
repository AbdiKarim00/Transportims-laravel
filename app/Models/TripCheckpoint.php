<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'checkpoint_name',
        'location',
        'arrival_time',
        'departure_time',
        'odometer_reading',
        'status',
        'notes'
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'odometer_reading' => 'decimal:2',
        'status' => 'boolean'
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
} 