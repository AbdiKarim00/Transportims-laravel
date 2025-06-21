<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_card_id',
        'driver_id',
        'vehicle_id',
        'trip_id',
        'transaction_date',
        'fuel_station_id',
        'fuel_type_id',
        'liters',
        'total_amount',
        'odometer_reading',
        'status',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'liters' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'odometer_reading' => 'decimal:2',
        'status' => 'boolean'
    ];

    public function fuelCard(): BelongsTo
    {
        return $this->belongsTo(FuelCard::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function fuelStation(): BelongsTo
    {
        return $this->belongsTo(FuelStation::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(FuelTransactionHistory::class);
    }
}
