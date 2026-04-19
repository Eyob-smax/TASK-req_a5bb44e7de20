<?php

namespace App\Enums;

enum PostState: string
{
    case Visible = 'visible';
    case Hidden  = 'hidden';
}
