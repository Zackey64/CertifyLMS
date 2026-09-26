<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingReservedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'meeting_reserved',
            'title' => 'ミーティングが予約されました',
            'body' => '新しいミーティングが予約されました。',
            'url' => route('meetings.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ミーティングが予約されました')
            ->line('新しいミーティングが予約されました。')
            ->action('ミーティングを確認する', route('meetings.index'));
    }
}
