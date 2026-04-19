<?php

namespace App\Enums;

enum CircuitBreakerMode: string
{
    case ReadWrite = 'read_write';
    case ReadOnly  = 'read_only';
}
