<?php

namespace App\Enums;

enum MessageStatusLabel: string
{
    case SinLeer = 'SIN LEER';
    case Notificado = 'NOTIFICADO';
    case Leido = 'LEÍDO';
    case Archivado = 'ARCHIVADO';
}
