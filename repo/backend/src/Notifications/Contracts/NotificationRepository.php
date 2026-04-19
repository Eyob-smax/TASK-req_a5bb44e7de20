<?php

declare(strict_types=1);

namespace CampusLearn\Notifications\Contracts;

interface NotificationRepository
{
    /**
     * @return array<string, int>  Map of category -> unread count (omit zero-count categories).
     */
    public function unreadCountsByCategory(int $userId): array;
}
