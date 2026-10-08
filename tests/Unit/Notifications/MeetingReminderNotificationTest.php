<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Enums\MeetingReminderType;
use App\Models\User;
use App\Notifications\MeetingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class MeetingReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_mail_has_eve_subject(): void
    {
        // Arrange
        $user = User::factory()->create();
        $notification = new MeetingReminderNotification(MeetingReminderType::Eve);
        // Act
        $mail = $notification->toMail($user);
        // Assert
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('明日のミーティングのお知らせ', $mail->subject);
    }

    public function test_to_mail_has_one_hour_before_subject(): void
    {
        // Arrange
        $user = User::factory()->create();
        $notification = new MeetingReminderNotification(MeetingReminderType::OneHourBefore);
        // Act
        $mail = $notification->toMail($user);
        // Assert
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('1時間後のミーティングのお知らせ', $mail->subject);
    }
}
