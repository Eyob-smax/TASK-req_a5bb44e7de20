<?php

namespace App\Enums;

enum BillScheduleSourceType: string
{
    case Enrollment = 'enrollment';
    case Service    = 'service';
    case Manual     = 'manual';
}
