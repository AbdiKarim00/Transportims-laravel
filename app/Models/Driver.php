<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'hire_date',
        'license_id',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function license()
    {
        return $this->belongsTo(DriverLicense::class, 'license_id');
    }

    public function driverStatuses()
    {
        return $this->hasMany(DriverStatus::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}