<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaReply;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は回答を登録できる
    public function test_student_can_store_reply(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $data = [
            'body' => '新規回答内容',
        ];
        // Act
        $response = $this->actingAs($student)->post(route('qa-board.replies.store', $thread), $data);
        // Assert
        $reply = QaReply::query()->where('user_id', $student->id)->firstOrFail();
        $this->assertSame($thread->id, $reply->qa_thread_id);
        $response->assertRedirect(route('qa-board.show', $thread));
    }

    // コーチは回答を登録できる
    public function test_coach_can_store_reply(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $thread = QaThread::factory()->create();
        $data = [
            'body' => '新規回答内容',
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('qa-board.replies.store', $thread), $data);
        // Assert
        $reply = QaReply::query()->where('user_id', $coach->id)->firstOrFail();
        $this->assertSame($thread->id, $reply->qa_thread_id);
        $response->assertRedirect(route('qa-board.show', $thread));
    }

    // 管理者は回答を登録できない
    public function test_admin_cannot_store_reply(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $thread = QaThread::factory()->create();
        $data = [
            'body' => '新規回答内容',
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('qa-board.replies.store', $thread), $data);
        // Assert
        $response->assertForbidden();
    }
}
