<?php

declare(strict_types=1);

namespace App\UseCases\Meeting;

use App\Enums\MeetingStatus;
use App\Exceptions\Mentoring\MeetingAlreadyStartedException;
use App\Exceptions\Mentoring\MeetingStatusTransitionException;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingCanceledNotification;
use App\Services\GoogleCalendarService;
use App\UseCases\MeetingQuota\RefundQuotaAction;
use Illuminate\Support\Facades\DB;

class CancelAction
{
    public function __construct(
        private readonly RefundQuotaAction $refundAction,
        private readonly GoogleCalendarService $googleCalendarService,
    ) {}

    public function __invoke(Meeting $meeting, User $actor): void
    {
        $eventId = DB::transaction(function () use ($meeting, $actor): ?string {
            $locked = Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || $locked->status !== MeetingStatus::Reserved) {
                throw MeetingStatusTransitionException::forCancel();
            }

            if ($locked->scheduled_at->lessThanOrEqualTo(now())) {
                throw new MeetingAlreadyStartedException;
            }

            $locked->update([
                'status' => MeetingStatus::Canceled->value,
                'canceled_by_user_id' => $actor->id,
                'canceled_at' => now(),
            ]);

            ($this->refundAction)($locked->student, $locked->id);

            return $locked->google_calendar_event_id;
        });

        // Google カレンダーに登録済みのイベントを削除する。
        $credential = $meeting->coach->googleCredential;

        if ($eventId !== null && $credential !== null) {
            $this->googleCalendarService->deleteMeetingEvent(
                $credential,
                $eventId,
            );
        }

        // 通知先はキャンセルした人以外
        $recipient = $actor->id === $meeting->student_id
            ? $meeting->coach
            : $meeting->student;

        $recipient->notify(new MeetingCanceledNotification);
    }
}
