<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaReply;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は回答を更新できる
    public function test_owner_can_update_reply(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = [
            'body' => '更新後内容',
        ];
        // Act
        $response = $this->actingAs($user)->patch(route('qa-board.replies.update', [$thread, $reply]), $data);
        // Assert
        $response->assertRedirect(route('qa-board.show', $thread));
        $this->assertDatabaseHas('qa_replies', [
            'body' => '更新後内容',
        ]);
    }

    // 作成者以外は回答を更新できない
    public function test_not_owner_cannot_update_reply(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        $data = [
            'body' => '更新後内容',
        ];
        // Act
        $response = $this->actingAs($user)->patch(route('qa-board.replies.update', [$thread, $reply]), $data);
        // Assert
        $response->assertForbidden();
    }
}
