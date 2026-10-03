<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingCanceledNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'meeting_canceled',
            'title' => 'ミーティングがキャンセルされました',
            'body' => '予定されていたミーティングがキャンセルされました。',
            'url' => route('meetings.index'),
        ];
    }
}
