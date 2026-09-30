<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプロフィールを更新できる
    public function test_student_can_update_profile(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $data = [
            'name' => 'Updated Name',
        ];
        // Act
        $response = $this->actingAs($student)->patch(route('settings.profile.update'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Updated Name',
        ]);
    }

    // コーチはプロフィールを更新できる
    public function test_coach_can_update_profile(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $data = [
            'name' => 'Updated Name',
        ];
        // Act
        $response = $this->actingAs($coach)->patch(route('settings.profile.update'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $coach->id,
            'name' => 'Updated Name',
        ]);
    }

    // 管理者はプロフィールを更新できる
    public function test_admin_can_update_profile(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $data = [
            'name' => 'Updated Name',
        ];
        // Act
        $response = $this->actingAs($admin)->patch(route('settings.profile.update'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Updated Name',
        ]);
    }
}
