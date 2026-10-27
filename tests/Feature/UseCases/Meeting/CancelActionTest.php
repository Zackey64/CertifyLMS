<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Enums\MeetingStatus;
use App\Models\GoogleCredential;
use App\Models\Meeting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\UseCases\Meeting\CancelAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CancelActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_cancel_meeting(): void
    {
        // arrange
        $user = User::factory()->create();
        $meeting = Meeting::factory()->reserved()->create([
            'student_id' => $user->id,
            'scheduled_at' => now()->addDay(),
        ]);
        // act
        $result = app(CancelAction::class)(
            $meeting,
            $user,
        );
        // assert
        $this->assertSame(
            MeetingStatus::Canceled,
            $meeting->fresh()->status,
        );
    }

    /**
     * Google カレンダー連携をモックして面談をキャンセルできることを確認するテスト
     *
     * @group external-api
     */
    public function test_can_cancel_meeting_mock_google_calendar(): void
    {
        // Arrange
        Notification::fake();

        $student = User::factory()->create();
        $coach = User::factory()->create();

        $credential = GoogleCredential::factory()->create([
            'user_id' => $coach->id,
        ]);

        $eventId = 'google-event-123';

        $meeting = Meeting::factory()->reserved()->create([
            'student_id' => $student->id,
            'coach_id' => $coach->id,
            'scheduled_at' => now()->addDay(),
            'google_calendar_event_id' => $eventId,
        ]);

        $this->mock(GoogleCalendarService::class)
            ->shouldReceive('deleteMeetingEvent')
            ->once()
            ->withArgs(fn ($actualCredential, $actualEventId) => $actualCredential->is($credential)
                && $actualEventId === $eventId
            );

        // Act
        app(CancelAction::class)(
            $meeting,
            $student,
        );

        // Assert
        $this->assertSame(
            MeetingStatus::Canceled,
            $meeting->fresh()->status,
        );
    }
}
