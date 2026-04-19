<?php

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Enums\BillType;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(5000, 200000);
        $tax      = (int) round($subtotal * 0.15);

        return [
            'user_id'          => User::factory(),
            'bill_schedule_id' => null,
            'type'             => BillType::Initial,
            'subtotal_cents'   => $subtotal,
            'tax_cents'        => $tax,
            'total_cents'      => $subtotal + $tax,
            'paid_cents'       => 0,
            'refunded_cents'   => 0,
            'status'           => BillStatus::Open,
            'issued_on'        => now()->format('Y-m-d'),
            'due_on'           => now()->addDays(30)->format('Y-m-d'),
            'past_due_at'      => null,
            'paid_at'          => null,
        ];
    }

    public function pastDue(): static
    {
        return $this->state(fn () => [
            'status'      => BillStatus::PastDue,
            'due_on'      => now()->subDays(20)->format('Y-m-d'),
            'past_due_at' => now()->subDays(10),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'status'     => BillStatus::Paid,
            'paid_cents' => $attrs['total_cents'] ?? 0,
            'paid_at'    => now(),
        ]);
    }

    public function recurring(): static
    {
        return $this->state(['type' => BillType::Recurring]);
    }

    public function penalty(): static
    {
        return $this->state(['type' => BillType::Penalty]);
    }
}
