<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_number',
        'card_type',
        'status',
        'issue_date',
        'expiry_date',
        'daily_limit',
        'monthly_limit',
        'assigned_driver_id',
        'assigned_vehicle_id',
        'fuel_type',
        'service_provider',
        'current_liters',
        'current_balance',
        'service_provider_id',
        'fuel_type_id'
    ];

    public function provider()
    {
        return $this->belongsTo(FuelCardProvider::class, 'service_provider_id');
    }

    public function cardType()
    {
        return $this->belongsTo(FuelCardType::class, 'card_type');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function transactions()
    {
        return $this->hasMany(FuelCardTransaction::class, 'fuel_card_id');
    }

    public function history()
    {
        return $this->hasMany(FuelCardHistory::class);
    }
}
