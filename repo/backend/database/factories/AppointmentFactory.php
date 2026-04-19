<?php

namespace Database\Factories;

use App\Enums\AppointmentResourceType;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+1 month');
        $end   = (clone $start)->modify('+30 minutes');

        return [
            'owner_user_id'   => User::factory(),
            'resource_type'   => AppointmentResourceType::RegistrarMeeting,
            'resource_ref'    => 'room-' . fake()->numberBetween(1, 30),
            'scheduled_start' => $start,
            'scheduled_end'   => $end,
            'status'          => AppointmentStatus::Scheduled,
            'notes'           => fake()->sentence(),
            'created_by'      => User::factory(),
        ];
    }

    public function canceled(): static
    {
        return $this->state(['status' => AppointmentStatus::Canceled]);
    }

    public function rescheduled(): static
    {
        return $this->state(['status' => AppointmentStatus::Rescheduled]);
    }
}
