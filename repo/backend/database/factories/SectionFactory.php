<?php

namespace Database\Factories;

use App\Enums\SectionStatus;
use App\Models\Course;
use App\Models\Section;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'course_id'    => Course::factory(),
            'term_id'      => Term::factory(),
            'section_code' => strtoupper(fake()->unique()->bothify('SEC-###')),
            'capacity'     => fake()->numberBetween(20, 80),
            'status'       => SectionStatus::Active,
        ];
    }

    public function archived(): static
    {
        return $this->state(['status' => SectionStatus::Archived]);
    }
}
