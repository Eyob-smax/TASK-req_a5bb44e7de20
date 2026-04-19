<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(5000, 200000);
        $tax      = (int) round($subtotal * 0.15);

        return [
            'user_id'        => User::factory(),
            'status'         => OrderStatus::PendingPayment,
            'subtotal_cents' => $subtotal,
            'tax_cents'      => $tax,
            'total_cents'    => $subtotal + $tax,
            'auto_close_at'  => now()->addMinutes(30),
            'paid_at'        => null,
            'canceled_at'    => null,
            'redeemed_at'    => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status'  => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn () => [
            'status'      => OrderStatus::Canceled,
            'canceled_at' => now(),
        ]);
    }

    public function pendingPayment(): static
    {
        return $this->state(['status' => OrderStatus::PendingPayment]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'auto_close_at' => now()->subMinutes(5),
        ]);
    }
}
