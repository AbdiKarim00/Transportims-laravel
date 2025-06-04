<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'office_id',
        'phone',
        'license_number'
    ];

    protected $hidden = ['password'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function interdictionRecords()
    {
        return $this->hasMany(DriverInterdictionRecord::class);
    }
}
