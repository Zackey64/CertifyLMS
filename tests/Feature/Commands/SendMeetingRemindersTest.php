<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Enums\MeetingReminderStatus;
use App\Enums\MeetingReminderType;
use App\Models\Meeting;
use App\Models\MeetingReminder;
use App\Notifications\MeetingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendMeetingRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_eve_reminder_sends_at_18(): void
    {
        // Arrange
        Notification::fake();

        $now = now()->setTime(18, 0);
        $this->travelTo($now);

        $meeting = Meeting::factory()->reserved()->create([
            'scheduled_at' => $now->copy()->addDay()->setTime(14, 0),
        ]);

        // Act
        $this->artisan('notifications:send-meeting-reminders', [
            '--window' => 'eve',
        ])->assertSuccessful();

        // Assert
        Notification::assertSentTo(
            $meeting->student,
            MeetingReminderNotification::class,
        );

        Notification::assertSentTo(
            $meeting->coach,
            MeetingReminderNotification::class,
        );
    }

    public function test_eve_reminder_sends_even_if_command_is_run_late(): void
    {
        // Arrange
        Notification::fake();

        $now = now()->setTime(20, 0);
        $this->travelTo($now);

        $meeting = Meeting::factory()->reserved()->create([
            'scheduled_at' => $now->copy()->addDay()->setTime(14, 0),
        ]);

        // Act
        $this->artisan('notifications:send-meeting-reminders', [
            '--window' => 'eve',
        ])->assertSuccessful();

        // Assert
        Notification::assertSentTo(
            $meeting->student,
            MeetingReminderNotification::class,
        );

        Notification::assertSentTo(
            $meeting->coach,
            MeetingReminderNotification::class,
        );
    }

    public function test_eve_reminder_does_not_send_before_18(): void
    {
        // Arrange
        Notification::fake();

        $now = now()->setTime(17, 59);
        $this->travelTo($now);

        Meeting::factory()->reserved()->create([
            'scheduled_at' => $now->copy()->addDay()->setTime(14, 0),
        ]);

        // Act
        $this->artisan('notifications:send-meeting-reminders', [
            '--window' => 'eve',
        ])->assertSuccessful();

        // Assert
        Notification::assertNothingSent();
    }

    public function test_eve_reminder_does_not_send_after_midnight(): void
    {
        // Arrange
        Notification::fake();

        $now = now()->startOfDay();
        $this->travelTo($now);

        Meeting::factory()->reserved()->create([
            'scheduled_at' => $now->copy()->addDay()->setTime(14, 0),
        ]);

        // Act
        $this->artisan('notifications:send-meeting-reminders', [
            '--window' => 'eve',
        ])->assertSuccessful();

        // Assert
        Notification::assertNothingSent();
    }

    public function test_eve_reminder_does_not_send_if_already_queued(): void
    {
        // Arrange
        Notification::fake();

        $now = now()->setTime(20, 0);
        $this->travelTo($now);

        $meeting = Meeting::factory()->reserved()->create([
            'scheduled_at' => $now->copy()->addDay()->setTime(14, 0),
        ]);

        foreach ([$meeting->student, $meeting->coach] as $recipient) {
            MeetingReminder::factory()->create([
                'meeting_id' => $meeting->id,
                'recipient_id' => $recipient->id,
                'type' => MeetingReminderType::Eve,
                'status' => MeetingReminderStatus::Queued,
                'sent_at' => null,
            ]);
        }

        // Act
        $this->artisan('notifications:send-meeting-reminders', [
            '--window' => 'eve',
        ])->assertSuccessful();

        // Assert
        Notification::assertNothingSent();
    }

    public function test_eve_reminder_does_not_send_if_already_sent(): void
    {
        // Arrange
        Notification::fake();

        $now = now()->setTime(20, 0);
        $this->travelTo($now);

        $meeting = Meeting::factory()->reserved()->create([
            'scheduled_at' => $now->copy()->addDay()->setTime(14, 0),
        ]);

        foreach ([$meeting->student, $meeting->coach] as $recipient) {
            MeetingReminder::factory()->create([
                'meeting_id' => $meeting->id,
                'recipient_id' => $recipient->id,
                'type' => MeetingReminderType::Eve,
                'status' => MeetingReminderStatus::Sent,
                'sent_at' => now(),
            ]);
        }

        // Act
        $this->artisan('notifications:send-meeting-reminders', [
            '--window' => 'eve',
        ])->assertSuccessful();

        // Assert
        Notification::assertNothingSent();
    }

    public function test_eve_reminder_retries_if_previous_attempt_failed(): void
    {
        // Arrange
        Notification::fake();

        $now = now()->setTime(20, 0);
        $this->travelTo($now);

        $meeting = Meeting::factory()->reserved()->create([
            'scheduled_at' => $now->copy()->addDay()->setTime(14, 0),
        ]);

        foreach ([$meeting->student, $meeting->coach] as $recipient) {
            MeetingReminder::factory()->create([
                'meeting_id' => $meeting->id,
                'recipient_id' => $recipient->id,
                'type' => MeetingReminderType::Eve,
                'status' => MeetingReminderStatus::Failed,
                'sent_at' => null,
            ]);
        }

        // Act
        $this->artisan('notifications:send-meeting-reminders', [
            '--window' => 'eve',
        ])->assertSuccessful();

        // Assert
        Notification::assertSentTo(
            $meeting->student,
            MeetingReminderNotification::class,
        );

        Notification::assertSentTo(
            $meeting->coach,
            MeetingReminderNotification::class,
        );

        $this->assertDatabaseHas('meeting_reminders', [
            'meeting_id' => $meeting->id,
            'type' => MeetingReminderType::Eve->value,
            'status' => MeetingReminderStatus::Queued->value,
        ]);
    }
}
