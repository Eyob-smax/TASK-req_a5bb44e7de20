<?php

declare(strict_types=1);

namespace CampusLearn\Billing;

use DateTimeImmutable;
use DateTimeZone;

final class BillScheduleCalculator
{
    public function __construct(
        private readonly int $dayOfMonth = 1,
        private readonly int $hourOfDay = 2,
    ) {
    }

    public function nextRunAt(BillScheduleSnapshot $snapshot, DateTimeImmutable $asOf): ?DateTimeImmutable
    {
        if ($snapshot->status !== 'active') {
            return null;
        }
        if ($snapshot->scheduleType !== 'recurring_monthly') {
            if ($snapshot->lastRunOn !== null) {
                return null;
            }
            $candidate = $this->normalizeToRunHour($snapshot->startOn);
            if ($candidate >= $asOf) {
                return $candidate;
            }
            return null;
        }

        $cursor = $snapshot->lastRunOn ?? $snapshot->startOn->modify('-1 month');
        $next = $this->nextMonthRun($cursor);
        if ($next < $this->normalizeToRunHour($snapshot->startOn)) {
            $next = $this->normalizeToRunHour($snapshot->startOn);
        }
        if ($snapshot->endOn !== null && $next > $snapshot->endOn) {
            return null;
        }
        return $next;
    }

    private function nextMonthRun(DateTimeImmutable $cursor): DateTimeImmutable
    {
        $tz = $cursor->getTimezone() ?: new DateTimeZone('UTC');
        $year = (int) $cursor->format('Y');
        $month = (int) $cursor->format('n');
        $month += 1;
        if ($month === 13) {
            $month = 1;
            $year += 1;
        }
        $iso = sprintf('%04d-%02d-%02d %02d:00:00', $year, $month, $this->dayOfMonth, $this->hourOfDay);
        return new DateTimeImmutable($iso, $tz);
    }

    private function normalizeToRunHour(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->setTime($this->hourOfDay, 0, 0);
    }
}
