<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はスレッド詳細画面を表示できる
    public function test_student_can_view_thread_show(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        // Act
        $response = $this->actingAs($student)->get(route('qa-board.show', $thread));
        // Assert
        $response->assertOk();
    }

    // コーチはスレッド詳細画面を表示できる
    public function test_coach_can_view_thread_show(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $thread = QaThread::factory()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('qa-board.show', $thread));
        // Assert
        $response->assertOk();
    }

    // 管理者はスレッド詳細画面を表示できない
    public function test_admin_cannot_view_thread_show(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $thread = QaThread::factory()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('qa-board.show', $thread));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は管理者用スレッド詳細画面を表示できる
    public function test_admin_can_view_thread_admin_show(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $thread = QaThread::factory()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.qa-board.show', $thread));
        // Assert
        $response->assertOk();
    }
}
