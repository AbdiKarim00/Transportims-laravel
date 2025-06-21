<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'category',
        'amount',
        'description',
        'receipt_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function expenseApproval(): HasOne
    {
        return $this->hasOne(ExpenseApproval::class, 'expense_id');
    }
}
