<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'subtotal_cents',
        'tax_cents',
        'total_cents',
        'auto_close_at',
        'paid_at',
        'canceled_at',
        'redeemed_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'auto_close_at' => 'datetime',
        'paid_at' => 'datetime',
        'canceled_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(OrderTimelineEvent::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PendingPayment);
    }

    public function scopeAutoCloseDue(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PendingPayment)
            ->whereNotNull('auto_close_at')
            ->where('auto_close_at', '<=', now());
    }
}
