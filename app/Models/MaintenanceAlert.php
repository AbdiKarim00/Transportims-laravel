<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'alert_type',
        'due_date',
        'severity',
        'status',
        'description',
        'notes'
    ];

    protected $casts = [
        'due_date' => 'datetime'
    ];

    /**
     * Get the vehicle that owns the maintenance alert.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
