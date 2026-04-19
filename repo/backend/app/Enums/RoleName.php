<?php

namespace App\Enums;

enum RoleName: string
{
    case Student       = 'student';
    case Teacher       = 'teacher';
    case Registrar     = 'registrar';
    case Administrator = 'administrator';
}
