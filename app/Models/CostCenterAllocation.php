<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCenterAllocation extends Model
{
    protected $table = 'cost_center_allocations';

    protected $fillable = [
        'cost_center_id',
        'vehicle_id',
        'allocation_percentage',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allocation_percentage' => 'decimal:2'
    ];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
