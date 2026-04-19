<?php

namespace App\Enums;

enum PenaltyJobStatus: string
{
    case Pending = 'pending';
    case Applied = 'applied';
    case Skipped = 'skipped';
}
