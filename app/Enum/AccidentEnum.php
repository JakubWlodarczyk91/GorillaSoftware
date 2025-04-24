<?php

namespace App\Enum;

enum AccidentEnum: string
{
    case SERVICE = 'przegląd';
    case REPORT_ACCIDENT = 'zgłoszenie awarii';
}
