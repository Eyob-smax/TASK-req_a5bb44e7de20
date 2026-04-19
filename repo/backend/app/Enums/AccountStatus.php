<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Active   = 'active';
    case Locked   = 'locked';
    case Disabled = 'disabled';
}
