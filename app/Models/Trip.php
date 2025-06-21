<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'route_id',
        'status', // now a string column
        'purpose_id',
        'start_time',
        'end_time',
        'start_location',
        'end_location',
        'distance',
        'fuel_used_litres',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'distance' => 'decimal:2',
        'fuel_used_litres' => 'decimal:2'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    // Removed status() relationship, use status string column directly

    public function purpose(): BelongsTo
    {
        return $this->belongsTo(TripPurpose::class, 'purpose_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(TripCheckpoint::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(TripExpense::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(TripPassenger::class);
    }

    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TripHistory::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'vehicle_id', 'vehicle_id')
            ->where('driver_id', $this->driver_id)
            ->whereDate('incident_date', DB::raw('DATE(start_time)'));
    }
}
