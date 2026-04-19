<?php

namespace Database\Factories;

use App\Enums\TermStatus;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Term>
 */
class TermFactory extends Factory
{
    protected $model = Term::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-6 months', '+1 month');
        $end   = (clone $start)->modify('+4 months');

        return [
            'name'      => 'Term ' . fake()->unique()->numerify('####'),
            'starts_on' => $start->format('Y-m-d'),
            'ends_on'   => $end->format('Y-m-d'),
            'status'    => TermStatus::Active,
        ];
    }

    public function upcoming(): static
    {
        return $this->state(['status' => TermStatus::Upcoming]);
    }

    public function archived(): static
    {
        return $this->state(['status' => TermStatus::Archived]);
    }
}
