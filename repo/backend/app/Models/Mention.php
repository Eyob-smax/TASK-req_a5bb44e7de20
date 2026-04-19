<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mention extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mentioned_user_id',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }
}
