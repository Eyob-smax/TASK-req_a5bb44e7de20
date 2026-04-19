<?php

namespace Database\Factories;

use App\Enums\PostState;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'thread_id'      => Thread::factory(),
            'author_id'      => User::factory(),
            'parent_post_id' => null,
            'body'           => fake()->paragraph(),
            'state'          => PostState::Visible,
            'edited_at'      => null,
        ];
    }

    public function hidden(): static
    {
        return $this->state(['state' => PostState::Hidden]);
    }
}
