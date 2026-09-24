<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // 作成者はスレッドを削除できる
    public function test_owner_can_destroy_thread(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $user->id,
        ]);
        // Act
        $response = $this->actingAs($user)->delete(route('qa-board.destroy', $thread));
        // Assert
        $response->assertRedirect(route('qa-board.index'));
        $this->assertDatabaseMissing('qa_threads', ['id' => $thread->id]);
    }

    // 作成者以外はスレッドを削除できない
    public function test_not_owner_cannot_destroy_thread(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        // Act
        $response = $this->actingAs($user)->delete(route('qa-board.destroy', $thread));
        // Assert
        $response->assertForbidden();
    }

    // 作成者のスレッドに回答がある場合は削除できない
    public function test_owner_cannot_destroy_thread_with_replies(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $user->id,
        ]);
        QaReply::factory()->create([
            'user_id' => $user->id,
            'qa_thread_id' => $thread->id,
        ]);
        // Act
        $response = $this->actingAs($user)->delete(route('qa-board.destroy', $thread));
        // Assert
        $response->assertForbidden();
    }

    // 管理者はスレッドを削除できる
    public function test_admin_can_destroy_thread(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $thread = QaThread::factory()->create();
        // Act
        $response = $this->actingAs($admin)->delete(route('admin.qa-board.destroy', $thread));
        // Assert
        $response->assertRedirect(route('admin.qa-board.index'));
        $this->assertDatabaseMissing('qa_threads', ['id' => $thread->id]);
    }
}
