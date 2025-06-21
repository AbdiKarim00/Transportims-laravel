<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'incident_type_id',
        'incident_status_id',
        'incident_severity_id',
        'incident_date',
        'location',
        'description',
        'reported_by',
        'investigated_by',
        'resolution_date',
        'resolution_notes',
        'status'
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'resolution_date' => 'datetime',
        'status' => 'boolean'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(IncidentType::class, 'incident_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(IncidentStatus::class, 'incident_status_id');
    }

    public function severity(): BelongsTo
    {
        return $this->belongsTo(IncidentSeverity::class, 'severity_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function investigatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(IncidentPhoto::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(IncidentRepair::class);
    }

    public function witnesses(): HasMany
    {
        return $this->hasMany(IncidentWitness::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(IncidentHistory::class);
    }
}
