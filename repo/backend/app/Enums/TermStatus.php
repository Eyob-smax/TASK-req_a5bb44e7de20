<?php

namespace App\Enums;

enum TermStatus: string
{
    case Upcoming = 'upcoming';
    case Active   = 'active';
    case Archived = 'archived';
}
