<?php

namespace App\Enums;

enum BillStatus: string
{
    case Open    = 'open';
    case Partial = 'partial';
    case Paid    = 'paid';
    case Void    = 'void';
    case PastDue = 'past_due';
}
