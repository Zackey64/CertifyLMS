<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプロフィール編集画面を表示できる
    public function test_student_can_view_profile_edit(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('settings.profile.edit'));
        // Assert
        $response->assertOk();
    }

    // コーチはプロフィール編集画面を表示できる
    public function test_coach_can_view_profile_edit(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('settings.profile.edit'));
        // Assert
        $response->assertOk();
    }

    // 管理者はプロフィール編集画面を表示できる
    public function test_admin_can_view_profile_edit(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('settings.profile.edit'));
        // Assert
        $response->assertOk();
    }
}
