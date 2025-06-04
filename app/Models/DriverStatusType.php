<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverStatusType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function driverStatuses()
    {
        return $this->hasMany(DriverStatus::class, 'status_type_id');
    }
}