<?php

namespace App\Enums;

enum BillType: string
{
    case Initial      = 'initial';
    case Recurring    = 'recurring';
    case Supplemental = 'supplemental';
    case Penalty      = 'penalty';
}
