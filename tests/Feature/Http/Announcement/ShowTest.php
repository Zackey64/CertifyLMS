<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Announcement;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は管理者お知らせ詳細画面を表示できない
    public function test_student_cannot_view_announcement_show(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $announcement = Announcement::factory()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.announcements.show', $announcement));
        // Assert
        $response->assertForbidden();
    }

    // コーチは管理者お知らせ詳細画面を表示できない
    public function test_coach_cannot_view_announcement_show(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $announcement = Announcement::factory()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('admin.announcements.show', $announcement));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は管理者お知らせ詳細画面を表示できる
    public function test_admin_can_view_announcement_show(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $announcement = Announcement::factory()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.announcements.show', $announcement));
        // Assert
        $response->assertOk();
    }
}
