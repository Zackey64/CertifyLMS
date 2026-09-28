<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Models\QaThread;
use App\Notifications\QaReplyReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class QaReplyReceivedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_mail_has_certify_lms_subject(): void
    {
        // Arrange
        $user = User::factory()->create();
        $thread = QaThread::factory()->create();
        $notification = new QaReplyReceivedNotification($thread);
        // Act
        $mail = $notification->toMail($user);
        // Assert
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame("質問掲示板の「{$thread->title}」に回答が届いています。", $mail->subject);
    }

    public function test_to_mail_action_url_includes_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $thread = QaThread::factory()->create();
        $notification = new QaReplyReceivedNotification($thread);
        // Act
        $mail = $notification->toMail($user);
        // Assert
        $this->assertStringContainsString('/qa', $mail->actionUrl);
    }
}
