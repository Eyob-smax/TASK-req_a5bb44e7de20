<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash          = 'cash';
    case Check         = 'check';
    case LocalTerminal = 'local_terminal';
    case Waiver        = 'waiver';
}
