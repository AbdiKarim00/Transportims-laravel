<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $table = 'cost_centers';

    protected $fillable = [
        'name',
        'code',
        'department_id',
        'status',
        'created_by',
        'updated_by'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function allocations()
    {
        return $this->hasMany(CostCenterAllocation::class);
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
