<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'employee_id',
        'email',
        'phone',
        'avatar_url',
        'hire_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(DriverLicense::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(DriverStatus::class, 'driver_id');
    }

    public function currentStatus()
    {
        return $this->hasOne(DriverStatus::class, 'driver_id')->latest();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(DriverRating::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    public function vehicleLogs(): HasMany
    {
        return $this->hasMany(VehicleLog::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(DriverHistory::class);
    }

    public function fuelCards(): HasMany
    {
        return $this->hasMany(FuelCard::class, 'assigned_to');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function tripExpenses(): HasMany
    {
        return $this->hasMany(TripExpense::class);
    }

    public function tripPassengers(): HasMany
    {
        return $this->hasMany(TripPassenger::class);
    }

    public function getCurrentStatusAttribute()
    {
        return $this->statuses()->latest()->first();
    }

    public function getCurrentLicenseAttribute()
    {
        return $this->licenses()->latest()->first();
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function getSafetyScoreAttribute()
    {
        return $this->ratings()->avg('safety_score') ?? 0;
    }

    public function getTotalTripsAttribute()
    {
        return $this->trips()->count();
    }

    public function getFuelEfficiencyAttribute()
    {
        return $this->trips()->avg('fuel_efficiency') ?? 0;
    }
}
