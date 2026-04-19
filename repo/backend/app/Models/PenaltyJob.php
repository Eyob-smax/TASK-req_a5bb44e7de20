<?php

namespace App\Models;

use App\Enums\PenaltyJobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenaltyJob extends Model
{
    protected $fillable = [
        'bill_id',
        'applied_at',
        'amount_cents',
        'status',
        'idempotency_key',
    ];

    protected $casts = [
        'status' => PenaltyJobStatus::class,
        'applied_at' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
