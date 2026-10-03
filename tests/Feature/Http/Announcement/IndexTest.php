<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Announcement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は管理者お知らせ画面を表示できない
    public function test_student_cannot_view_announcement_index(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.announcements.index'));
        // Assert
        $response->assertForbidden();
    }

    // コーチは管理者お知らせ画面を表示できない
    public function test_coach_cannot_view_announcement_index(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('admin.announcements.index'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は管理者お知らせ画面を表示できる
    public function test_admin_can_view_announcement_index(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.announcements.index'));
        // Assert
        $response->assertOk();
    }
}
