<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はスレッドを登録できる
    public function test_student_can_store_thread(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $certification = Certification::factory()->create();
        $data = [
            'certification_id' => $certification->id,
            'title' => '新規スレッドタイトル',
            'body' => '新規スレッド内容',
        ];
        // Act
        $response = $this->actingAs($student)->post(route('qa-board.store'), $data);
        // Assert
        $thread = QaThread::query()->where('user_id', $student->id)->firstOrFail();
        $response->assertRedirect(route('qa-board.show', $thread));
    }

    // コーチはスレッドを登録できない
    public function test_coach_cannot_store_thread(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $certification = Certification::factory()->create();
        $data = [
            'certification_id' => $certification->id,
            'title' => '新規スレッドタイトル',
            'body' => '新規スレッド内容',
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('qa-board.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者はスレッドを登録できない
    public function test_admin_cannot_store_thread(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->create();
        $data = [
            'certification_id' => $certification->id,
            'title' => '新規スレッドタイトル',
            'body' => '新規スレッド内容',
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('qa-board.store'), $data);
        // Assert
        $response->assertForbidden();
    }
}
