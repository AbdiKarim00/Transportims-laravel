<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'date',
        'amount',
        'cost',
        'odometer_reading',
        'notes'
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:2',
        'cost' => 'decimal:2',
        'odometer_reading' => 'decimal:2'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
