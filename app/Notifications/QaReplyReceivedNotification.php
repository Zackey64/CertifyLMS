<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\QaThread;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QaReplyReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly QaThread $qaThread) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'qa_reply_received',
            'title' => "質問掲示板の「{$this->qaThread->title}」に回答が届いています。",
            'body' => "質問掲示板の「{$this->qaThread->title}」に回答が届いています。",
            'url' => route('qa-board.show', $this->qaThread),
        ];
    }
}
