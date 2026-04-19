<?php

namespace App\Enums;

enum ScopeType: string
{
    case Global    = 'global';
    case Term      = 'term';
    case Course    = 'course';
    case Section   = 'section';
    case GradeItem = 'grade_item';
}
