<?php

namespace App\Models;

use App\Enums\AppointmentResourceType;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'resource_type',
        'resource_ref',
        'scheduled_start',
        'scheduled_end',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'resource_type' => AppointmentResourceType::class,
        'status' => AppointmentStatus::class,
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
