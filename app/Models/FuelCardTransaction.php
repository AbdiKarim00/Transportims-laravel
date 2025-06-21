<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelCardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_card_id',
        'transaction_date',
        'amount_currency',
        'fuel_type_id',
        'amount_litres',
        'odometer_reading',
        'location',
        'vendor',
        'receipt_image_path',
    ];

    public function fuelCard()
    {
        return $this->belongsTo(FuelCard::class);
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class);
    }
}
