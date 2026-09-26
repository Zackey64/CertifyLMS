<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QaReplyReceivedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'qa_reply_received',
            'title' => '新しい回答があります',
            'body' => 'あなたの質問に新しい回答が届いています。',
            'url' => route('qa-board.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('新しい回答があります')
            ->line('あなたの質問に新しい回答が届いています。')
            ->action('回答を確認する', route('qa-board.index'));
    }
}
