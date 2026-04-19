<?php

declare(strict_types=1);

namespace Tests\Domain\Billing;

use CampusLearn\Billing\BillScheduleCalculator;
use CampusLearn\Billing\BillScheduleSnapshot;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BillScheduleCalculatorTest extends TestCase
{
    public function testRecurringMonthlyNextRunIsFirstOfNextMonthAt2AM(): void
    {
        $calc = new BillScheduleCalculator(1, 2);
        $snapshot = new BillScheduleSnapshot(
            scheduleType: 'recurring_monthly',
            status: 'active',
            startOn: new DateTimeImmutable('2026-03-01 02:00:00'),
            endOn: null,
            lastRunOn: new DateTimeImmutable('2026-04-01 02:00:00'),
        );
        $next = $calc->nextRunAt($snapshot, new DateTimeImmutable('2026-04-10 00:00:00'));
        $this->assertNotNull($next);
        $this->assertSame('2026-05-01 02:00:00', $next->format('Y-m-d H:i:s'));
    }

    public function testRecurringStopsAtEndDate(): void
    {
        $calc = new BillScheduleCalculator();
        $snapshot = new BillScheduleSnapshot(
            scheduleType: 'recurring_monthly',
            status: 'active',
            startOn: new DateTimeImmutable('2026-01-01 02:00:00'),
            endOn: new DateTimeImmutable('2026-04-15 00:00:00'),
            lastRunOn: new DateTimeImmutable('2026-04-01 02:00:00'),
        );
        $next = $calc->nextRunAt($snapshot, new DateTimeImmutable('2026-04-10 00:00:00'));
        $this->assertNull($next);
    }

    public function testClosedScheduleReturnsNull(): void
    {
        $calc = new BillScheduleCalculator();
        $snapshot = new BillScheduleSnapshot(
            scheduleType: 'recurring_monthly',
            status: 'closed',
            startOn: new DateTimeImmutable('2026-01-01 02:00:00'),
            endOn: null,
            lastRunOn: null,
        );
        $this->assertNull($calc->nextRunAt($snapshot, new DateTimeImmutable('2026-04-10 00:00:00')));
    }

    public function testLeapYearBoundary(): void
    {
        $calc = new BillScheduleCalculator(1, 2);
        $snapshot = new BillScheduleSnapshot(
            scheduleType: 'recurring_monthly',
            status: 'active',
            startOn: new DateTimeImmutable('2028-01-01 02:00:00'),
            endOn: null,
            lastRunOn: new DateTimeImmutable('2028-02-01 02:00:00'),
        );
        $next = $calc->nextRunAt($snapshot, new DateTimeImmutable('2028-02-15 00:00:00'));
        $this->assertSame('2028-03-01 02:00:00', $next->format('Y-m-d H:i:s'));
    }
}
