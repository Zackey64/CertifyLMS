<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AnnouncementNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Announcement $announcement)
    {
        $this->id = (string) Str::uuid();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'admin_announcement',
            'title' => $this->announcement->title,
            'body' => $this->announcement->body,
            'announcement_id' => $this->announcement->id,
            'url' => route('notifications.show', $this->id),
        ];
    }
}
