<?php

namespace App\Enums;

enum BillScheduleType: string
{
    case OneTime          = 'one_time';
    case RecurringMonthly = 'recurring_monthly';
    case Supplemental     = 'supplemental';
    case Penalty          = 'penalty';
}
