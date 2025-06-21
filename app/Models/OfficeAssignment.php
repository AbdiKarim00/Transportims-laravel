<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'vehicle_id',
        'assignment_date',
        'return_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'return_date' => 'date',
        'status' => 'boolean'
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
} 