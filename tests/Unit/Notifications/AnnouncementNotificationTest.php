<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_channel(): void
    {
        // Arrange
        $user = User::factory()->create();
        $announcement = Announcement::factory()->create();
        $notification = new AnnouncementNotification($announcement);
        // Act
        $channels = $notification->via($user);
        // Assert
        $this->assertSame(['database'], $channels);
    }

    public function test_to_database_contains_announcement_id(): void
    {
        // Arrange
        $user = User::factory()->create();
        $announcement = Announcement::factory()->create();
        $notification = new AnnouncementNotification($announcement);
        // Act
        $database = $notification->toDatabase($user);
        // Assert
        $this->assertIsArray($database);
        $this->assertArrayHasKey('announcement_id', $database);
        $this->assertSame($announcement->id, $database['announcement_id']);
    }
}
