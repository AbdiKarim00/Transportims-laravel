<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleOperatingCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'period_start',
        'period_end',
        'fuel_costs',
        'maintenance_costs',
        'trip_expenses',
        'insurance_costs',
        'total_costs',
        'revenue',
        'profit_loss'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'fuel_costs' => 'decimal:2',
        'maintenance_costs' => 'decimal:2',
        'trip_expenses' => 'decimal:2',
        'insurance_costs' => 'decimal:2',
        'total_costs' => 'decimal:2',
        'revenue' => 'decimal:2',
        'profit_loss' => 'decimal:2'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Calculate the ROI for this period
     */
    public function calculateROI(): float
    {
        if ($this->total_costs == 0) {
            return 0;
        }
        return ($this->profit_loss / $this->total_costs) * 100;
    }

    /**
     * Get the cost per kilometer for this period
     */
    public function getCostPerKilometer(): float
    {
        $totalDistance = $this->vehicle->trips()
            ->whereBetween('start_date', [$this->period_start, $this->period_end])
            ->sum('distance');

        if ($totalDistance == 0) {
            return 0;
        }

        return $this->total_costs / $totalDistance;
    }
}
