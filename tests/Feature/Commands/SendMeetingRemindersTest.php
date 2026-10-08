<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

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

    public function test_eve_reminder_sends_even_if_command_is_run_late(): void
    {
        // Arrange
        Notification::fake();
        $meeting = Meeting::factory()->reserved()->create([
            'scheduled_at' => now()->addHours(20),
        ]);
        // Act
        $this->artisan('notifications:send-meeting-reminders', ['--window' => 'eve'])->assertSuccessful();
        Notification::assertSentTo(
            $meeting->student,
            MeetingReminderNotification::class
        );
        Notification::assertSentTo(
            $meeting->coach,
            MeetingReminderNotification::class
        );
        // Assert
        $this->assertDatabaseHas('meeting_reminders', [
            'meeting_id' => $meeting->id,
            'type' => MeetingReminderType::Eve->value,
        ]);
    }

    public function test_eve_reminder_does_not_send_if_already_sent(): void
    {
        // Arrange
        Notification::fake();
        $meeting = Meeting::factory()->reserved()->create([
            'scheduled_at' => now()->addHours(20),
        ]);
        MeetingReminder::factory()->create([
            'meeting_id' => $meeting->id,
            'type' => MeetingReminderType::Eve,
            'sent_at' => now(),
        ]);
        // Act
        $this->artisan('notifications:send-meeting-reminders', ['--window' => 'eve'])->assertSuccessful();
        // Assert
        Notification::assertNothingSent();
    }
}
