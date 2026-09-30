<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Password;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はパスワードを更新できる
    public function test_student_can_update_password(): void
    {
        // Arrange
        $student = User::factory()->student()->create([
            'password' => 'current_password',
        ]);
        $data = [
            'current_password' => 'current_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];
        // Act
        $response = $this->actingAs($student)->put(route('settings.password.update'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
        ]);
    }

    // コーチはパスワードを更新できる
    public function test_coach_can_update_password(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create([
            'password' => 'current_password',
        ]);
        $data = [
            'current_password' => 'current_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];
        // Act
        $response = $this->actingAs($coach)->put(route('settings.password.update'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $coach->id,
        ]);
    }

    // 管理者はパスワードを更新できる
    public function test_admin_can_update_password(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create([
            'password' => 'current_password',
        ]);
        $data = [
            'current_password' => 'current_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];
        // Act
        $response = $this->actingAs($admin)->put(route('settings.password.update'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}
