<?php

namespace Database\Factories;

use App\Enums\NotificationCategory;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        return [
            'type'           => fake()->unique()->slug(3),
            'category'       => NotificationCategory::System,
            'title_template' => 'Event: {title}',
            'body_template'  => 'Details: {body}',
        ];
    }
}
