<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\MeetingReminderStatus;
use App\Enums\MeetingReminderType;
use App\Models\MeetingReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class MeetingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly MeetingReminderType $type,
        private readonly string $reminderId,
    ) {
        $this->afterCommit();
    }

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

    /**
     * 通知ジョブが失敗した場合に呼び出される。
     */
    public function failed(Throwable $exception): void
    {
        MeetingReminder::query()
            ->whereKey($this->reminderId)
            ->update([
                'status' => MeetingReminderStatus::Failed->value,
            ]);
    }
}
