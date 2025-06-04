<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'status_type_id',
        'start_date',
        'end_date',
        'reason',
        'notes',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function statusType()
    {
        return $this->belongsTo(DriverStatusType::class, 'status_type_id');
    }
}