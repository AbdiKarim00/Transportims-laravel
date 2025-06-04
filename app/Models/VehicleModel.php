<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'make_id',
        'name',
        'year',
        'type',
    ];

    public function vehicleMake()
    {
        return $this->belongsTo(VehicleMake::class, 'make_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}