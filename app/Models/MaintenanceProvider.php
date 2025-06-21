<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
    ];

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'provider_id');
    }
}
