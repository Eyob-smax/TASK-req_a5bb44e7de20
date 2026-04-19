<?php

namespace App\Models;

use App\Enums\BackupStatus;
use Illuminate\Database\Eloquent\Model;

class BackupJob extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'scheduled_for',
        'file_path',
        'file_size_bytes',
        'checksum_sha256',
        'status',
        'retention_expires_on',
        'completed_at',
    ];

    protected $casts = [
        'status' => BackupStatus::class,
        'scheduled_for' => 'datetime',
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
        'retention_expires_on' => 'date',
    ];
}
