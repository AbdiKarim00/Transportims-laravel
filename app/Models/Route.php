<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_location',
        'end_location',
        'distance',
        'estimated_time',
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_routes');
    }
}