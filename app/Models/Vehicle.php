<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $fillable = [
        'registration_no',
        'vin',
        'make_id',
        'model_id',
        'type_id',
        'year',
        'color',
        'fuel_type_id',
        'transmission',
        'engine_size',
        'odometer_reading',
        'last_maintenance_date',
        'next_maintenance_date',
        'department_id',
        'office_id',
        'assigned_user_id',
        'purchase_date',
        'purchase_price',
        'current_value',
        'depreciation_rate',
        'annual_depreciation',
        'accumulated_depreciation',
        'net_book_value',
        'disposal_date',
        'disposal_value',
        'asset_condition',
        'notes'
    ];

    protected $casts = [
        'year' => 'integer',
        'engine_size' => 'decimal:2',
        'odometer_reading' => 'integer',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'annual_depreciation' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'net_book_value' => 'decimal:2',
        'disposal_value' => 'decimal:2',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_date' => 'date',
        'disposal_date' => 'date'
    ];

    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    // Removed status() relationship, use status string column directly if needed

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'vehicle_assignments')
            ->withPivot(['assignment_date', 'return_date', 'assignment_reason', 'notes'])
            ->withTimestamps();
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'vehicle_routes')
            ->withPivot(['assignment_date', 'end_date', 'notes'])
            ->withTimestamps();
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function maintenanceRecords(): HasMany
    {
        // Now points to maintenance_schedules table via MaintenanceRecord model
        return $this->hasMany(MaintenanceRecord::class, 'vehicle_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function insurancePolicies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VehicleLog::class);
    }

    public function fuelCards(): BelongsToMany
    {
        return $this->belongsToMany(FuelCard::class, 'vehicle_fuel_card_assignments')
            ->withPivot(['assignment_date', 'return_date', 'notes'])
            ->withTimestamps();
    }

    public function media(): HasMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Calculate the current depreciation for the vehicle
     *
     * @throws \App\Exceptions\DepreciationCalculationException
     */
    public function calculateDepreciation()
    {
        if (!$this->purchase_date || !$this->purchase_price || !$this->depreciation_rate) {
            throw new \App\Exceptions\DepreciationCalculationException(
                "Cannot calculate depreciation: missing purchase date, purchase price, or depreciation rate."
            );
        }

        $yearsOwned = now()->diffInYears($this->purchase_date);
        $this->annual_depreciation = $this->purchase_price * ($this->depreciation_rate / 100);
        $this->accumulated_depreciation = $this->annual_depreciation * $yearsOwned;
        $this->net_book_value = $this->purchase_price - $this->accumulated_depreciation;

        return [
            'annual_depreciation' => $this->annual_depreciation,
            'accumulated_depreciation' => $this->accumulated_depreciation,
            'net_book_value' => $this->net_book_value
        ];
    }

    /**
     * Get total operating costs for a date range
     */
    public function getOperatingCosts($startDate, $endDate)
    {
        $fuelCosts = $this->fuelTransactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $maintenanceCosts = $this->maintenanceRecords()
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->sum('estimated_cost');

        $tripExpenses = $this->tripExpenses()
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        return [
            'fuel_costs' => $fuelCosts,
            'maintenance_costs' => $maintenanceCosts,
            'trip_expenses' => $tripExpenses,
            'total' => $fuelCosts + $maintenanceCosts + $tripExpenses
        ];
    }
}
