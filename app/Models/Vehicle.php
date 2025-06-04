<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $fillable = [
        'vin',
        'license_plate',
        'make',
        'model',
        'year',
        'current_mileage',
        'last_maintenance_date',
        'fuel_type_id',
        'status_id'
    ];

    public function fuelTransactions()
    {
        return $this->hasMany(FuelTransaction::class);
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
