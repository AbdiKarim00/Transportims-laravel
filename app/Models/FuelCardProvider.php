<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelCardProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone_number',
        'email',
        'website',
    ];

    public function fuelCards()
    {
        return $this->hasMany(FuelCard::class);
    }
}