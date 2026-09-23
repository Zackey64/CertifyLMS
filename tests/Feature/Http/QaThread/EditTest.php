<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    // 作成者はスレッド編集画面を表示できる
    public function test_owner_can_view_thread_edit(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $user->id,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('qa-board.edit', $thread));
        // Assert
        $response->assertOk();
    }

    // 作成者以外はスレッド編集画面を表示できない
    public function test_not_owner_cannot_view_thread_edit(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('qa-board.edit', $thread));
        // Assert
        $response->assertForbidden();
    }
}
