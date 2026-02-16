<?php

namespace App\Enums;

enum MessageStatusCode: string
{
    case Deposited = 'DEPOSITED';
    case Notified = 'NOTIFIED';
    case Read = 'READ';
    case Archived = 'ARCHIVED';
}
