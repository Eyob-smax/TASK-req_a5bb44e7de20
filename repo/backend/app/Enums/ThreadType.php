<?php

namespace App\Enums;

enum ThreadType: string
{
    case Announcement = 'announcement';
    case Discussion   = 'discussion';
}
