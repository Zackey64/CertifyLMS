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

                // 送信済みならスキップ
                if ($reminder->status === MeetingReminderStatus::Sent) {
                    continue;
                }

                // 送信待ち・失敗の場合は通知をキューに登録
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
            // 翌日0時から翌々日0時まで
            $tomorrow = $now->copy()->addDay()->startOfDay();
            $dayAfterTomorrow = $tomorrow->copy()->addDay();

            $query
                ->where('scheduled_at', '>=', $tomorrow)
                ->where('scheduled_at', '<', $dayAfterTomorrow);
        } else {
            $targetTime = $now->copy()->addHour();

            $query
                ->where('scheduled_at', '>=', $targetTime)
                ->where('scheduled_at', '<', $targetTime->copy()->addMinute());
        }

        return $query->get();
    }
}
