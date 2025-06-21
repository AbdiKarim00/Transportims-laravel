<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverStatusType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function driverStatuses(): HasMany
    {
        return $this->hasMany(DriverStatus::class, 'status_type_id');
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class, 'status_id');
    }
}
