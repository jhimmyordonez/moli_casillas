<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Active = 'ACTIVO';
    case Suspended = 'SUSPENDIDO';
}
