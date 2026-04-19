<?php

declare(strict_types=1);

namespace CampusLearn\Notifications;

use CampusLearn\Notifications\Contracts\NotificationRepository;

final class UnreadCounter
{
    public function __construct(
        private readonly NotificationRepository $repository,
    ) {
    }

    /**
     * @return array{total: int, by_category: array<string, int>}
     */
    public function summarize(int $userId): array
    {
        $byCategory = $this->repository->unreadCountsByCategory($userId);
        $total = array_sum($byCategory);
        return [
            'total' => $total,
            'by_category' => $byCategory,
        ];
    }
}
