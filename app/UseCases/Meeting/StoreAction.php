<?php

declare(strict_types=1);

namespace App\UseCases\Meeting;

use App\Enums\MeetingStatus;
use App\Exceptions\MeetingQuota\InsufficientMeetingQuotaException;
use App\Exceptions\Mentoring\MeetingNoAvailableCoachException;
use App\Models\Certification;
use App\Models\Enrollment;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingReservedNotification;
use App\Services\CoachMeetingLoadService;
use App\Services\GoogleCalendarService;
use App\Services\MeetingAvailabilityService;
use App\Services\MeetingQuotaService;
use App\UseCases\MeetingQuota\ConsumeQuotaAction;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StoreAction
{
    public function __construct(
        private readonly MeetingAvailabilityService $availabilityService,
        private readonly CoachMeetingLoadService $coachLoadService,
        private readonly MeetingQuotaService $quotaService,
        private readonly ConsumeQuotaAction $consumeAction,
        private readonly GoogleCalendarService $googleCalendarService,
    ) {}

    /**
     * 受講生の面談を予約する。
     */
    public function __invoke(
        Enrollment $enrollment,
        Carbon $scheduledAt,
        ?string $topic,
    ): Meeting {
        $student = $enrollment->user;

        $meeting = DB::transaction(function () use (
            $enrollment,
            $student,
            $scheduledAt,
            $topic,
        ): Meeting {
            if ($this->quotaService->remaining($student) < 1) {
                throw new InsufficientMeetingQuotaException;
            }

            $certification = $enrollment->certification;

            $this->availabilityService->validateSlot(
                $certification,
                $scheduledAt,
            );

            $candidates = $this->findAvailableCoaches(
                $certification,
                $scheduledAt,
            );

            if ($candidates->isEmpty()) {
                throw new MeetingNoAvailableCoachException;
            }

            $coach = $this->coachLoadService->leastLoadedCoach($candidates);

            try {
                $meeting = Meeting::create([
                    'enrollment_id' => $enrollment->id,
                    'coach_id' => $coach->id,
                    'student_id' => $student->id,
                    'scheduled_at' => $scheduledAt,
                    'status' => MeetingStatus::Reserved->value,
                    'topic' => $topic,
                    'meeting_url_snapshot' => $coach->meeting_url,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                throw new MeetingNoAvailableCoachException($e);
            }

            $transaction = ($this->consumeAction)($student, $meeting->id);

            $meeting->update([
                'meeting_quota_transaction_id' => $transaction->id,
            ]);

            return $meeting->fresh();
        });

        // Google カレンダーに面談予定を登録する。
        $credential = $meeting->coach->googleCredential;

        if ($credential !== null) {
            $eventId = $this->googleCalendarService->createMeetingEvent(
                $credential,
                $meeting,
            );

            $meeting->update([
                'google_calendar_event_id' => $eventId,
            ]);
        }

        // 予約確定後にコーチへ通知する。
        $meeting->coach->notify(new MeetingReservedNotification);

        return $meeting;
    }

    /**
     * 指定時刻に対応可能なコーチを取得する。
     *
     * @return Collection<int, User>
     */
    private function findAvailableCoaches(
        Certification $certification,
        Carbon $scheduledAt,
    ): Collection {
        $time = $scheduledAt->format('H:i:s');

        return $certification->coaches()
            ->whereHas('coachAvailabilities', function ($query) use (
                $scheduledAt,
                $time,
            ) {
                $query->where('day_of_week', $scheduledAt->dayOfWeek)
                    ->where('is_active', true)
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>', $time);
            })
            ->whereDoesntHave('meetingsAsCoach', function ($query) use (
                $scheduledAt,
            ) {
                $query->where('scheduled_at', $scheduledAt)
                    ->whereIn('status', [
                        MeetingStatus::Reserved->value,
                        MeetingStatus::Completed->value,
                    ]);
            })
            ->get();
    }
}
