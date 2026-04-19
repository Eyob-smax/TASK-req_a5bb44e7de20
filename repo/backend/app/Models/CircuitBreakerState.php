<?php

namespace App\Models;

use App\Enums\CircuitBreakerMode;
use Illuminate\Database\Eloquent\Model;

class CircuitBreakerState extends Model
{
    protected $table = 'circuit_breaker_state';
    public $timestamps = false;

    protected $fillable = [
        'mode',
        'tripped_at',
        'tripped_reason',
        'updated_at',
    ];

    protected $casts = [
        'mode' => CircuitBreakerMode::class,
        'tripped_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
