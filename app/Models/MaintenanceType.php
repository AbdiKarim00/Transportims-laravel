<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_critical',
        'frequency_days',
        'estimated_cost'
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'frequency_days' => 'integer',
        'estimated_cost' => 'decimal:2'
    ];

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }
}
