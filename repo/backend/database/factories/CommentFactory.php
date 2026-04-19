<?php

namespace Database\Factories;

use App\Enums\PostState;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id'   => Post::factory(),
            'author_id' => User::factory(),
            'body'      => fake()->sentence(),
            'state'     => PostState::Visible,
            'edited_at' => null,
        ];
    }

    public function hidden(): static
    {
        return $this->state(['state' => PostState::Hidden]);
    }
}
