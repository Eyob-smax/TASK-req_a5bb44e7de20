<?php

declare(strict_types=1);

namespace CampusLearn\Notifications\Contracts;

interface NotificationWriter
{
    /**
     * Persists a single notification row for a recipient. Returns the new row id.
     *
     * @param array<string, mixed> $payload
     */
    public function write(
        int $userId,
        string $category,
        string $type,
        string $title,
        string $body,
        array $payload,
    ): int;
}
