<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'license_number',
        'issue_date',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function isExpired()
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon()
    {
        return $this->expiry_date->isFuture() &&
            $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getStatusAttribute()
    {
        if ($this->isExpired()) {
            return 'Expired';
        } elseif ($this->isExpiringSoon()) {
            return 'Expiring Soon';
        } else {
            return 'Valid';
        }
    }
}
