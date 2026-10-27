<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Enums\MeetingStatus;
use App\Models\CoachAvailability;
use App\Models\Enrollment;
use App\Models\GoogleCredential;
use App\Models\Meeting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\UseCases\Meeting\StoreAction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reserve_meeting(): void
    {
        // arrange
        $enrollment = Enrollment::factory()->create();
        $student = $enrollment->user;
        $certification = $enrollment->certification;
        $coach = $enrollment->coach;
        // コーチを作成
        $coach = User::factory()->create();

        // コーチを資格に関連付ける
        $certification->coaches()->syncWithoutDetaching([
            $coach->id => [
                'assigned_by_user_id' => $student->id,
                'assigned_at' => now(),
            ],
        ]);

        $student->update([
            'max_meetings' => 1,
        ]);
        // 予約日時を設定
        $scheduledAt = Carbon::tomorrow()->setTime(10, 0, 0);

        // コーチの対応可能時間を設定
        CoachAvailability::factory()->create([
            'coach_id' => $coach->id,
            'day_of_week' => $scheduledAt->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);
        // act
        $result = app(StoreAction::class)(
            $enrollment,
            $scheduledAt,
            'テスト予約',
        );
        // assert
        $this->assertInstanceOf(Meeting::class, $result);
        $this->assertSame(MeetingStatus::Reserved, $result->status);
        $this->assertSame($enrollment->id, $result->enrollment_id);
        $this->assertEquals($scheduledAt, $result->scheduled_at);
    }

    /**
     * Google カレンダー連携をモックして面談を予約できることを確認するテスト
     *
     * @group external-api
     */
    public function test_can_reserve_meeting_mock_google_calendar(): void
    {
        // Arrange
        $enrollment = Enrollment::factory()->create();
        $student = $enrollment->user;
        $certification = $enrollment->certification;
        $coach = User::factory()->create();

        $certification->coaches()->syncWithoutDetaching([
            $coach->id => [
                'assigned_by_user_id' => $student->id,
                'assigned_at' => now(),
            ],
        ]);

        $student->update([
            'max_meetings' => 1,
        ]);

        $scheduledAt = Carbon::tomorrow()->setTime(10, 0, 0);

        CoachAvailability::factory()->create([
            'coach_id' => $coach->id,
            'day_of_week' => $scheduledAt->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        // Google カレンダー連携情報を作成
        $credential = GoogleCredential::create([
            'user_id' => $coach->id,
            'google_user_id' => 'test-google-user',
            'calendar_id' => 'primary',
            'access_token' => json_encode([
                'access_token' => 'test-access-token',
                'expires_in' => 3600,
            ], JSON_THROW_ON_ERROR),
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);

        // Google Calendar API は呼び出さず、空き時間とイベント ID をモック
        $this->mock(GoogleCalendarService::class)
            ->shouldReceive('getBusyIntervals')
            ->once()
            ->withArgs(fn ($actualCredential, $start, $end) => $actualCredential->is($credential)
            )
            ->andReturn([])

            ->shouldReceive('createMeetingEvent')
            ->once()
            ->withArgs(fn ($actualCredential, $actualMeeting) => $actualCredential->is($credential)
                && $actualMeeting->coach_id === $coach->id
            )
            ->andReturn('google-event-123');

        // Act
        $result = app(StoreAction::class)(
            $enrollment,
            $scheduledAt,
            'テスト予約',
        );

        // Assert
        $this->assertInstanceOf(Meeting::class, $result);
        $this->assertSame(MeetingStatus::Reserved, $result->status);
        $this->assertSame($enrollment->id, $result->enrollment_id);
        $this->assertEquals($scheduledAt, $result->scheduled_at);

        $this->assertDatabaseHas('meetings', [
            'id' => $result->id,
            'google_calendar_event_id' => 'google-event-123',
        ]);
    }
}
