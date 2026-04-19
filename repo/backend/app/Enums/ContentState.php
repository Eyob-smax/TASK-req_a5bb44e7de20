<?php

namespace App\Enums;

enum ContentState: string
{
    case Visible = 'visible';
    case Hidden  = 'hidden';
    case Locked  = 'locked';
}
