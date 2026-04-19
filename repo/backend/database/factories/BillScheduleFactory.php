<?php

namespace Database\Factories;

use App\Enums\BillScheduleSourceType;
use App\Enums\BillScheduleStatus;
use App\Enums\BillScheduleType;
use App\Models\BillSchedule;
use App\Models\FeeCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillSchedule>
 */
class BillScheduleFactory extends Factory
{
    protected $model = BillSchedule::class;

    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'source_type'     => BillScheduleSourceType::Service,
            'source_id'       => null,
            'schedule_type'   => BillScheduleType::RecurringMonthly,
            'amount_cents'    => 500000,
            'fee_category_id' => FeeCategory::factory(),
            'start_on'        => now()->subMonth()->format('Y-m-d'),
            'end_on'          => null,
            'status'          => BillScheduleStatus::Active,
            'next_run_on'     => now()->format('Y-m-d'),
        ];
    }

    public function dueToday(): static
    {
        return $this->state(['next_run_on' => now()->format('Y-m-d')]);
    }

    public function paused(): static
    {
        return $this->state(['status' => BillScheduleStatus::Paused]);
    }

    public function closed(): static
    {
        return $this->state(['status' => BillScheduleStatus::Closed]);
    }
}
