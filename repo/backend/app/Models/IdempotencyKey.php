<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'scope',
        'key_hash',
        'request_fingerprint',
        'result_status',
        'result_body',
        'expires_at',
    ];

    protected $casts = [
        'result_body' => 'array',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
