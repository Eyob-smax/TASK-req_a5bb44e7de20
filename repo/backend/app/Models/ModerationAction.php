<?php

namespace App\Models;

use App\Enums\ModerationActionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationAction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'moderator_id',
        'target_type',
        'target_id',
        'action',
        'notes',
    ];

    protected $casts = [
        'action' => ModerationActionType::class,
        'created_at' => 'datetime',
    ];

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
