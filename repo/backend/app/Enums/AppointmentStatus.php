<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled   = 'scheduled';
    case Rescheduled = 'rescheduled';
    case Canceled    = 'canceled';
    case Completed   = 'completed';
}
