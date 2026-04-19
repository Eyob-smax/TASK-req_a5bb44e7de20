<?php

namespace App\Enums;

enum DrDrillOutcome: string
{
    case Passed  = 'passed';
    case Failed  = 'failed';
    case Partial = 'partial';
}
