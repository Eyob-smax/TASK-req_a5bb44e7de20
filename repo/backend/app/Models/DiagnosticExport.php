<?php

namespace App\Models;

use App\Enums\DiagnosticExportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosticExport extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'initiated_by',
        'file_path',
        'file_size_bytes',
        'checksum_sha256',
        'encryption_key_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'status' => DiagnosticExportStatus::class,
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
