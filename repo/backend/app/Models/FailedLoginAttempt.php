<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedLoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = ['email', 'ip', 'attempted_at'];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];
}
