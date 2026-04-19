<?php

namespace App\Models;

use App\Enums\OrderTimelineEvent as OrderTimelineEventEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTimelineEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'event',
        'actor_user_id',
        'payload',
    ];

    protected $casts = [
        'event' => OrderTimelineEventEnum::class,
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
