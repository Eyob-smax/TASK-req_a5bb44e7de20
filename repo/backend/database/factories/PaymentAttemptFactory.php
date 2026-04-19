<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentAttempt>
 */
class PaymentAttemptFactory extends Factory
{
    protected $model = PaymentAttempt::class;

    public function definition(): array
    {
        return [
            'order_id'         => Order::factory(),
            'operator_user_id' => User::factory(),
            'method'           => PaymentMethod::Cash,
            'amount_cents'     => fake()->numberBetween(1000, 50000),
            'status'           => PaymentStatus::Pending,
            'completed_at'     => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status'       => PaymentStatus::Succeeded,
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status'       => PaymentStatus::Failed,
            'completed_at' => now(),
        ]);
    }
}
