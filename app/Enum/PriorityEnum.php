<?php

namespace App\Enum;

enum PriorityEnum: string
{
    case NORMAL = 'normalny';
    case CRITICAL = 'krytyczny';
    case HIGH = 'wysoki';
}
