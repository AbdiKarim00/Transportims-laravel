<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'service_provider_id',
        'maintenance_type',
        'scheduled_date',
        'status',
        'description',
        'estimated_cost',
        'notes'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'estimated_cost' => 'decimal:2'
    ];

    /**
     * Get the vehicle that owns the maintenance schedule.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the service provider that owns the maintenance schedule.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    // Removed status_id and status() relationship, use status string column directly
}
