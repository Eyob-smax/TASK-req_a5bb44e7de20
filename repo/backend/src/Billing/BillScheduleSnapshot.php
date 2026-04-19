<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

use DateTimeImmutable;

final class BillScheduleSnapshot
{
    public function __construct(
        public readonly string $scheduleType,
        public readonly string $status,
        public readonly DateTimeImmutable $startOn,
        public readonly ?DateTimeImmutable $endOn,
        public readonly ?DateTimeImmutable $lastRunOn,
    ) {
    }
}
