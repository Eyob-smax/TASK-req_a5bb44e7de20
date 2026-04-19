<?php

namespace App\Models;

use App\Enums\DrDrillOutcome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrDrillRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'drill_date',
        'operator_user_id',
        'outcome',
        'notes',
    ];

    protected $casts = [
        'outcome' => DrDrillOutcome::class,
        'drill_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }
}
