<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverStatus extends Model
{
    use HasFactory;

    protected $table = 'driver_statuses';

    protected $fillable = [
        'driver_id',
        'status',
        'start_date',
        'end_date',
        'reason',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public static function getCurrentStatus($driverId)
    {
        return static::where('driver_id', $driverId)
            ->latest()
            ->first();
    }

    public static function updateStatus($driverId, $status, $notes = null)
    {
        return static::create([
            'driver_id' => $driverId,
            'status' => $status,
            'start_date' => now(),
            'notes' => $notes,
        ]);
    }
}
