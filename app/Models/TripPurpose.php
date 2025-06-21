<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripPurpose extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'notes'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
} 