<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は通知一覧画面を表示できる
    public function test_student_can_view_notification_index(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('notifications.index'));
        // Assert
        $response->assertOk();
    }

    // コーチは通知一覧画面を表示できる
    public function test_coach_can_view_notification_index(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('notifications.index'));
        // Assert
        $response->assertOk();
    }

    // 管理者は通知一覧画面を表示できない
    public function test_admin_cannot_view_notification_index(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('notifications.index'));
        // Assert
        $response->assertForbidden();
    }
}
