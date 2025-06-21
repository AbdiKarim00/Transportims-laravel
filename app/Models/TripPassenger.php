<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'driver_id',
        'passenger_name',
        'passenger_phone',
        'passenger_email',
        'pickup_location',
        'dropoff_location',
        'pickup_time',
        'dropoff_time',
        'status',
        'notes'
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'dropoff_time' => 'datetime',
        'status' => 'boolean'
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
} 