<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingCanceledNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ミーティングがキャンセルされました')
            ->line('予定されていたミーティングがキャンセルされました。')
            ->action('ミーティングを確認する', route('meetings.index'));
    }
}
