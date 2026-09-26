<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChatMessageReceivedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'chat_message_received',
            'title' => '新しいメッセージがあります',
            'body' => 'チャットに新しいメッセージが届いています。',
            'url' => route('chat.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('新しいメッセージがあります')
            ->line('チャットに新しいメッセージが届いています。')
            ->action('チャットを確認する', route('chat.index'));
    }
}
