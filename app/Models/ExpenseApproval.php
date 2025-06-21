<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseApproval extends Model
{
    protected $table = 'expense_approvals';

    protected $fillable = [
        'expense_id',
        'approver_id',
        'status',
        'comments',
        'approval_date'
    ];

    protected $casts = [
        'approval_date' => 'datetime'
    ];

    public function expense()
    {
        return $this->belongsTo(TripExpense::class, 'expense_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
