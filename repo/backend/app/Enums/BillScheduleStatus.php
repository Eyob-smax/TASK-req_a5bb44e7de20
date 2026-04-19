<?php

namespace App\Enums;

enum BillScheduleStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Closed = 'closed';
}
