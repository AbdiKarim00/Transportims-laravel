<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMake extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_of_origin',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}