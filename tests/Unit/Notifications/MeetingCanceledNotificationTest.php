<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\MeetingCanceledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingCanceledNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_channel(): void
    {
        // Arrange
        $user = User::factory()->create();
        $notification = new MeetingCanceledNotification;
        // Act
        $channels = $notification->via($user);
        // Assert
        $this->assertSame(['database'], $channels);
    }

    public function test_to_database_contains_url(): void
    {
        // Arrange
        $user = User::factory()->create();
        $notification = new MeetingCanceledNotification;
        // Act
        $database = $notification->toDatabase($user);
        // Assert
        $this->assertStringContainsString('/meetings', $database['url']);
    }
}
