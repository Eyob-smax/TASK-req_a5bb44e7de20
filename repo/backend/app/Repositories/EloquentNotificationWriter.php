<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Notification;
use CampusLearn\Notifications\Contracts\NotificationWriter;

final class EloquentNotificationWriter implements NotificationWriter
{
    public function write(
        int $userId,
        string $category,
        string $type,
        string $title,
        string $body,
        array $payload,
    ): int {
        $notification = Notification::create([
            'user_id'    => $userId,
            'category'   => $category,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'payload'    => $payload,
            'read_at'    => null,
            'created_at' => now(),
        ]);
        return (int) $notification->id;
    }
}
