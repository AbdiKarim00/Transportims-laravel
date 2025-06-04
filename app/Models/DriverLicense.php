<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'issue_date',
        'expiry_date',
        'country',
        'state_province',
        'license_type',
        'endorsements',
        'restrictions',
        'document_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}