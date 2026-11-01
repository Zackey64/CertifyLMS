<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\MeetingReminderStatus;
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
            foreach ([$meeting->student, $meeting->coach] as $recipient) {
                $reminder = MeetingReminder::firstOrCreate(
                    [
                        'meeting_id' => $meeting->id,
                        'type' => $type,
                        'recipient_id' => $recipient->id,
                    ],
                    [
                        'status' => MeetingReminderStatus::Pending,
                        'sent_at' => null,
                    ],
                );

                // Pending または Failed の場合だけキュー登録を試みる
                $claimed = MeetingReminder::query()
                    ->whereKey($reminder->id)
                    ->whereIn('status', [
                        MeetingReminderStatus::Pending->value,
                        MeetingReminderStatus::Failed->value,
                    ])
                    ->update([
                        'status' => MeetingReminderStatus::Queued->value,
                    ]);

                // すでにキュー登録済み・送信済みならスキップ
                if ($claimed === 0) {
                    continue;
                }

                $recipient->notify(
                    new MeetingReminderNotification($type, $reminder->id)
                );
            }
        }

        return self::SUCCESS;
    }

    private function targetMeetings(MeetingReminderType $type)
    {
        $now = now()->startOfMinute();

        $query = Meeting::query()
            ->where('status', MeetingStatus::Reserved)
            ->with(['student', 'coach']);

        if ($type === MeetingReminderType::Eve) {
            // 前日18時より前なら対象外
            if ($now->hour < 18) {
                return collect();
            }

            // 翌日の面談を取得
            $tomorrow = $now->copy()->addDay()->startOfDay();
            $dayAfterTomorrow = $tomorrow->copy()->addDay();

            $query
                ->where('scheduled_at', '>=', $tomorrow)
                ->where('scheduled_at', '<', $dayAfterTomorrow);
        } else {
            // 1時間後に開始する面談を取得
            $targetTime = $now->copy()->addHour();

            $query
                ->where('scheduled_at', '>=', $targetTime)
                ->where('scheduled_at', '<', $targetTime->copy()->addMinute());
        }

        return $query->get();
    }
}
