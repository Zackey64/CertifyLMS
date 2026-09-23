<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はスレッド登録フォームを表示できる
    public function test_student_can_view_thread_create(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('qa-board.create'));
        // Assert
        $response->assertOk();
    }

    // コーチはスレッド登録フォームを表示できない
    public function test_coach_cannot_view_thread_create(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('qa-board.create'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者はスレッド登録フォームを表示できない
    public function test_admin_cannot_view_thread_create(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('qa-board.create'));
        // Assert
        $response->assertForbidden();
    }
}
