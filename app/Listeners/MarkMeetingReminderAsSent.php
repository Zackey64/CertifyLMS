<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Notifications\MeetingReminderNotification;
use Illuminate\Notifications\Events\NotificationSent;

class MarkMeetingReminderAsSent
{
    public function handle(NotificationSent $event): void
    {
        // メール送信成功時だけ更新
        if (
            ! $event->notification instanceof MeetingReminderNotification
            || $event->channel !== 'mail'
        ) {
            return;
        }

        $event->notification->markAsSent();
    }
}
