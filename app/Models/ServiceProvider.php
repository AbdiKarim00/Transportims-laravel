<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'phone',
        'email',
        'contact_person',
        'status',
        'notes'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function incidentRepairs(): HasMany
    {
        return $this->hasMany(IncidentRepair::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'provider_id');
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'service_provider_id');
    }
}
