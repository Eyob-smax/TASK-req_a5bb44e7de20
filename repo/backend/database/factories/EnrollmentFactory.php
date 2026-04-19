<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'section_id'   => Section::factory(),
            'status'       => EnrollmentStatus::Enrolled,
            'enrolled_at'  => now(),
            'withdrawn_at' => null,
        ];
    }

    public function withdrawn(): static
    {
        return $this->state([
            'status'       => EnrollmentStatus::Withdrawn,
            'withdrawn_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(['status' => EnrollmentStatus::Completed]);
    }
}
