<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\MeetingReminderType;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingReminder;
use App\Notifications\MeetingReminderNotification;
use Illuminate\Console\Command;

class SendMeetingReminders extends Command
{
    protected $signature = 'notifications:send-meeting-reminders
    {--window= : eve or one_hour_before}';

    protected $description = 'Send meeting reminders based on the specified window';

    public function handle()
    {
        $window = $this->option('window');

        $type = match ($window) {
            'eve' => MeetingReminderType::Eve,
            'one_hour_before' => MeetingReminderType::OneHourBefore,
            default => null,
        };
        if ($type === null) {
            $this->error('Invalid window. Use eve or one_hour_before.');

            return self::FAILURE;
        }

        // 対象面談を取得
        $meetings = $this->targetMeetings($type);
        foreach ($meetings as $meeting) {
            $alreadySent = MeetingReminder::query()
                ->where('meeting_id', $meeting->id)
                ->where('type', $type->value)
                ->exists();
            // 重複チェック
            if ($alreadySent) {
                continue;
            }

            $notification = new MeetingReminderNotification($type);

            // student / coach に通知
            $meeting->student->notify($notification);
            $meeting->coach->notify($notification);

            // 送信履歴を保存
            MeetingReminder::create([
                'meeting_id' => $meeting->id,
                'type' => $type,
                'sent_at' => now(),
            ]);
        }

        return self::SUCCESS;
    }

    private function targetMeetings(MeetingReminderType $type)
    {
        return match ($type) {
            MeetingReminderType::Eve => Meeting::query()
                ->where('status', MeetingStatus::Reserved)
                ->where('scheduled_at', '<=', now()->addDay())
                ->where('scheduled_at', '>', now())
                ->get(),

            MeetingReminderType::OneHourBefore => Meeting::query()
                ->where('status', MeetingStatus::Reserved)
                ->where('scheduled_at', '<=', now()->addHour())
                ->where('scheduled_at', '>', now())
                ->get(),
        };
    }
}
