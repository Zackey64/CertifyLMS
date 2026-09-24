<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 作成者はスレッドを更新できる
    public function test_owner_can_update_thread(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = [
            'title' => '更新後タイトル',
            'body' => '更新後内容',
        ];
        // Act
        $response = $this->actingAs($user)->patch(route('qa-board.update', $thread), $data);
        // Assert
        $response->assertRedirect(route('qa-board.show', $thread));
        $this->assertDatabaseHas('qa_threads', ['id' => $thread->id]);
    }

    // 作成者以外はスレッドを更新できない
    public function test_not_owner_cannot_update_thread(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        $data = [
            'title' => '更新後タイトル',
            'body' => '更新後内容',
        ];
        // Act
        $response = $this->actingAs($user)->patch(route('qa-board.update', $thread), $data);
        // Assert
        $response->assertForbidden();
    }
}
