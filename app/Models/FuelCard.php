<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_number',
        'provider_id',
        'card_type_id',
        'status_id',
        'issue_date',
        'expiry_date',
        'pin_code',
        'vehicle_id',
        'driver_id',
        'daily_limit',
        'monthly_limit',
    ];

    public function provider()
    {
        return $this->belongsTo(FuelCardProvider::class, 'provider_id');
    }

    public function cardType()
    {
        return $this->belongsTo(FuelCardType::class, 'card_type_id');
    }

    public function status()
    {
        return $this->belongsTo(FuelCardStatus::class, 'status_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function transactions()
    {
        return $this->hasMany(FuelCardTransaction::class);
    }

    public function history()
    {
        return $this->hasMany(FuelCardHistory::class);
    }
}