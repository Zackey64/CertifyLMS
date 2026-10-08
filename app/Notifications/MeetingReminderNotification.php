<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\MeetingReminderType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingReminderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly MeetingReminderType $type) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'meeting_reminder',
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => route('meetings.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->line($this->body())
            ->action('ミーティングを確認する', route('meetings.index'));
    }

    private function title(): string
    {
        return match ($this->type) {
            MeetingReminderType::Eve => '明日のミーティングのお知らせ',
            MeetingReminderType::OneHourBefore => '1時間後のミーティングのお知らせ',
        };
    }

    private function body(): string
    {
        return match ($this->type) {
            MeetingReminderType::Eve => '明日、ミーティングが予定されています。',
            MeetingReminderType::OneHourBefore => '1時間後にミーティングが予定されています。',
        };
    }
}
