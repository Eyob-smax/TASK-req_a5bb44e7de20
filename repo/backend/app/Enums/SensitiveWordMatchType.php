<?php

namespace App\Enums;

enum SensitiveWordMatchType: string
{
    case Exact     = 'exact';
    case Substring = 'substring';
}
