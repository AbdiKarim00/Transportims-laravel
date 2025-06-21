<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDepreciationHistory extends Model
{
    use HasFactory;

    protected $table = 'vehicle_depreciation_history';

    protected $fillable = [
        'vehicle_id',
        'calculation_date',
        'purchase_price',
        'depreciation_rate',
        'annual_depreciation',
        'accumulated_depreciation',
        'net_book_value'
    ];

    protected $casts = [
        'calculation_date' => 'date',
        'purchase_price' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'annual_depreciation' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'net_book_value' => 'decimal:2'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
