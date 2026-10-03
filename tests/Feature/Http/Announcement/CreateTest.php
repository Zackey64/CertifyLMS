<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Announcement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は管理者お知らせ追加画面を表示できない
    public function test_student_cannot_view_announcement_create(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.announcements.create'));
        // Assert
        $response->assertForbidden();
    }

    // コーチは管理者お知らせ追加画面を表示できない
    public function test_coach_cannot_view_announcement_create(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('admin.announcements.create'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は管理者お知らせ追加画面を表示できる
    public function test_admin_can_view_announcement_create(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.announcements.create'));
        // Assert
        $response->assertOk();
    }
}
