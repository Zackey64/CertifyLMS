<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaReply;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は回答を削除できる
    public function test_owner_can_destroy_reply(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create([
            'user_id' => $user->id,
        ]);
        // Act
        $response = $this->actingAs($user)->delete(route('qa-board.replies.destroy', [$thread, $reply]));
        // Assert
        $response->assertRedirect(route('qa-board.show', $thread));
        $this->assertDatabaseMissing('qa_replies', ['id' => $reply->id]);
    }

    // 作成者以外は回答を削除できない
    public function test_not_owner_cannot_destroy_reply(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        // Act
        $response = $this->actingAs($user)->delete(route('qa-board.replies.destroy', [$thread, $reply]));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は回答を削除できる
    public function test_admin_can_destroy_reply(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create();
        // Act
        $response = $this->actingAs($admin)->delete(route('admin.qa-board.replies.destroy', [$thread, $reply]));
        // Assert
        $response->assertRedirect(route('admin.qa-board.show', $thread));
        $this->assertDatabaseMissing('qa_replies', ['id' => $reply->id]);
    }
}
