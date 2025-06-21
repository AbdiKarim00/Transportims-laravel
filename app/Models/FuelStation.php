<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'status',
        'notes'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }
} 