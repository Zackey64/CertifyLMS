<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Avatar;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はアイコン画像を追加できる
    public function test_student_can_store_avatar(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $data = [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ];
        // Act
        $response = $this->actingAs($student)->post(route('settings.avatar.store'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
        ]);
    }

    // コーチはアイコン画像を追加できる
    public function test_coach_can_store_avatar(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $data = [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('settings.avatar.store'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $coach->id,
        ]);
    }

    // 管理者はアイコン画像を追加できる
    public function test_admin_can_store_avatar(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $data = [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('settings.avatar.store'), $data);
        // Assert
        $response->assertRedirect(route('settings.profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}
