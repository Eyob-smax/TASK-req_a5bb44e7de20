<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Notification;
use CampusLearn\Notifications\Contracts\NotificationRepository;

final class EloquentNotificationRepository implements NotificationRepository
{
    public function unreadCountsByCategory(int $userId): array
    {
        $rows = Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->selectRaw('category, COUNT(*) AS c')
            ->groupBy('category')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $key        = is_object($row->category) ? $row->category->value : (string) $row->category;
            $map[$key]  = (int) $row->c;
        }
        return $map;
    }
}
