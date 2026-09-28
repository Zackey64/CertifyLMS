<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Models\ChatRoom;
use App\Notifications\ChatMessageReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class ChatMessageReceivedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_mail_has_certify_lms_subject(): void
    {
        // Arrange
        $user = User::factory()->create();
        $room = ChatRoom::factory()->create();
        $notification = new ChatMessageReceivedNotification($room);
        // Act
        $mail = $notification->toMail($user);
        // Assert
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame("chatの「{$room->enrollment->certification->name}」にメッセージが届いています。", $mail->subject);
    }

    public function test_to_mail_action_url_includes_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $room = ChatRoom::factory()->create();
        $notification = new ChatMessageReceivedNotification($room);
        // Act
        $mail = $notification->toMail($user);
        // Assert
        $this->assertStringContainsString('/chat', $mail->actionUrl);
    }
}
