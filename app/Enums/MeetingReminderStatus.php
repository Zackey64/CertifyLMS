<?php

declare(strict_types=1);

namespace App\Enums;

enum MeetingReminderStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Queued = 'queued';
    case Failed = 'failed';
}
