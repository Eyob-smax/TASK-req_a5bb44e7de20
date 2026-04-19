<?php

declare(strict_types=1);

namespace CampusLearn\Notifications;

use CampusLearn\Notifications\Contracts\NotificationWriter;

final class NotificationDispatcher
{
    public function __construct(
        private readonly NotificationWriter $writer,
    ) {
    }

    /**
     * Fan out a notification to all recipients. Each recipient receives one row.
     *
     * @param int[] $recipientIds
     * @param array<string, mixed> $payload
     * @return int[] list of created notification ids.
     */
    public function dispatch(
        array $recipientIds,
        string $category,
        string $type,
        string $title,
        string $body,
        array $payload = [],
    ): array {
        $ids = [];
        foreach (array_unique($recipientIds) as $recipient) {
            $ids[] = $this->writer->write(
                (int) $recipient,
                $category,
                $type,
                $title,
                $body,
                $payload,
            );
        }
        return $ids;
    }
}
