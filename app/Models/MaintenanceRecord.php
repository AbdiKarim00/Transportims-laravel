<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $table = 'maintenance_schedules';

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'scheduled_date',
        'status',
        'description',
        'estimated_cost',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'estimated_cost' => 'decimal:2'
    ];

    // Removed status() relationship, use status string column directly
}
