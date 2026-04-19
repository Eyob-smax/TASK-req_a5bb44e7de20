<?php

namespace Database\Factories;

use App\Enums\ContentState;
use App\Enums\ThreadType;
use App\Models\Course;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        return [
            'course_id'   => Course::factory(),
            'section_id'  => null,
            'author_id'   => User::factory(),
            'thread_type' => ThreadType::Discussion,
            'qa_enabled'  => false,
            'title'       => fake()->sentence(6),
            'body'        => fake()->paragraph(),
            'state'       => ContentState::Visible,
            'edited_at'   => null,
        ];
    }

    public function announcement(): static
    {
        return $this->state(['thread_type' => ThreadType::Announcement]);
    }

    public function qa(): static
    {
        return $this->state(['qa_enabled' => true]);
    }

    public function hidden(): static
    {
        return $this->state(['state' => ContentState::Hidden]);
    }

    public function locked(): static
    {
        return $this->state(['state' => ContentState::Locked]);
    }
}
