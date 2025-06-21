<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelTransactionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_transaction_id',
        'action',
        'description',
        'changed_by',
        'old_values',
        'new_values'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function fuelTransaction(): BelongsTo
    {
        return $this->belongsTo(FuelTransaction::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
} 