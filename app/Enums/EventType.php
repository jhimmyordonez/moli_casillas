<?php

namespace App\Enums;

enum EventType: string
{
    case Deposited = 'DEPOSITED';
    case Notified = 'NOTIFIED';
    case Read = 'READ';
    case Downloaded = 'DOWNLOADED';
    case Archived = 'ARCHIVED';
}
