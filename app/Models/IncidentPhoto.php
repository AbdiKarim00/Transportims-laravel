<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'uploaded_by',
        'upload_date'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'upload_date' => 'datetime'
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
} 