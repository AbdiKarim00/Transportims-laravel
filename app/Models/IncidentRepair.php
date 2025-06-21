<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentRepair extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'repair_date',
        'repair_cost',
        'repair_description',
        'service_provider_id',
        'repair_status',
        'completed_by',
        'completion_date',
        'notes'
    ];

    protected $casts = [
        'repair_date' => 'date',
        'repair_cost' => 'decimal:2',
        'completion_date' => 'datetime',
        'repair_status' => 'boolean'
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
} 