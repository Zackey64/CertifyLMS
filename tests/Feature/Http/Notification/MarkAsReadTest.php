<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Notification;

use App\Models\User;
use App\Notifications\ChatMessageReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkAsReadTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は通知を既読にできる
    public function test_student_can_mark_as_read(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $notification = new ChatMessageReceivedNotification;
        $databaseNotification = $student->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $notification::class,
            'data' => $notification->toDatabase($student),
            'read_at' => null,
        ]);
        // Act
        $response = $this->actingAs($student)->post(route('notifications.markAsRead', $databaseNotification));
        // Assert
        $response->assertRedirect();
        $this->assertNotNull($databaseNotification->fresh()->read_at);
    }

    // コーチは通知を既読にできる
    public function test_coach_can_mark_as_read(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $notification = new ChatMessageReceivedNotification;
        $databaseNotification = $coach->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $notification::class,
            'data' => $notification->toDatabase($coach),
            'read_at' => null,
        ]);
        // Act
        $response = $this->actingAs($coach)->post(route('notifications.markAsRead', $databaseNotification));
        // Assert
        $response->assertRedirect();
        $this->assertNotNull($databaseNotification->fresh()->read_at);
    }

    // 管理者は通知を既読にできない
    public function test_admin_cannot_mark_as_read(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $notification = new ChatMessageReceivedNotification;
        $databaseNotification = $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $notification::class,
            'data' => $notification->toDatabase($admin),
            'read_at' => null,
        ]);
        // Act
        $response = $this->actingAs($admin)->post(route('notifications.markAsRead', $databaseNotification));
        // Assert
        $response->assertForbidden();
    }
}
