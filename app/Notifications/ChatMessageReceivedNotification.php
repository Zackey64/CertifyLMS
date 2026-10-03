<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ChatRoom;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChatMessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ChatRoom $room) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'chat_message_received',
            'title' => "chatの「{$this->room->enrollment->certification->name}」にメッセージが届いています。",
            'body' => "chatの「{$this->room->enrollment->certification->name}」にメッセージが届いています。",
            'url' => route('chat.show', $this->room),
        ];
    }
}
