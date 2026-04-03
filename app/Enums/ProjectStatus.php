<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case OPEN = 'open';
    case WAITING_PAYMENT = 'waiting_payment';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';
    case CANCELLED = 'cancelled';
}
