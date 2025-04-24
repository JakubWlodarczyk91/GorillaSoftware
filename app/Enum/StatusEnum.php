<?php

namespace App\Enum;

enum StatusEnum: string
{
    case NEW = 'nowy';
    case PLANNED = 'zaplanowano';
    case DEADLINE = 'termin';
}
