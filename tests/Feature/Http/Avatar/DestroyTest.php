<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Avatar;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はアイコン画像を削除できる
    public function test_student_can_destroy_avatar(): void
    {
        // Arrange
        $student = User::factory()->student()->create();

        // Act
        $response = $this->actingAs($student)->delete(route('settings.avatar.destroy'));
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
        ]);
    }

    // コーチはアイコン画像を削除できる
    public function test_coach_can_destroy_avatar(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->delete(route('settings.avatar.destroy'));
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $coach->id,
        ]);
    }

    // 管理者はアイコン画像を削除できる
    public function test_admin_can_destroy_avatar(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->delete(route('settings.avatar.destroy'));
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}
