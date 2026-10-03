<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Notification;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は通知の詳細を表示できる
    public function test_student_can_view_show(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $announcement = Announcement::factory()->create();
        $notification = new AnnouncementNotification($announcement);
        $databaseNotification = $student->notifications()->create([
            'id' => $notification->id,
            'type' => $notification::class,
            'data' => $notification->toDatabase($student),
            'read_at' => null,
        ]);
        // Act
        $response = $this->actingAs($student)->get(route('notifications.show', $databaseNotification));
        // Assert
        $response->assertOk();
    }
}
