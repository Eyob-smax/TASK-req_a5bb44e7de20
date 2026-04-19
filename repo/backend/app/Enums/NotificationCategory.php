<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case Announcements = 'announcements';
    case Mentions      = 'mentions';
    case Billing       = 'billing';
    case System        = 'system';
}
