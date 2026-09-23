<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaReply;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は回答の編集画面を表示できる
    public function test_owner_can_edit_reply(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create([
            'user_id' => $user->id,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('qa-board.replies.edit', [$thread, $reply]));
        // Assert
        $response->assertOk();
    }

    // 作成者以外は回答の編集画面を表示できない
    public function test_not_owner_cannot_edit_reply(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('qa-board.replies.edit', [$thread, $reply]));
        // Assert
        $response->assertForbidden();
    }
}
