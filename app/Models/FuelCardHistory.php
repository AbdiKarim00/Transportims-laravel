<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelCardHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_card_id',
        'change_date',
        'description',
        'changed_by_user_id',
    ];

    public function fuelCard()
    {
        return $this->belongsTo(FuelCard::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}