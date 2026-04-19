<?php

namespace App\Enums;

enum ModerationActionType: string
{
    case Hide    = 'hide';
    case Restore = 'restore';
    case Lock    = 'lock';
    case Unlock  = 'unlock';
}
