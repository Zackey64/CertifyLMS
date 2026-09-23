<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Enums\QaThreadStatus;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnresolveTest extends TestCase
{
    use RefreshDatabase;

    // 作成者はスレッドを未解決にできる
    public function test_owner_can_unresolve_thread(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $user->id,
            'status' => QaThreadStatus::Resolved,
        ]);
        // Act
        $response = $this->actingAs($user)->post(route('qa-board.unresolve', $thread));
        // Assert
        $response->assertRedirect(route('qa-board.show', $thread));
        $this->assertSame(QaThreadStatus::Unresolved, $thread->fresh()->status);
    }

    // 作成者以外はスレッドを未解決にできない
    public function test_not_owner_cannot_unresolve_thread(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->create([
            'user_id' => $otherUser->id,
            'status' => QaThreadStatus::Unresolved,
        ]);
        // Act
        $response = $this->actingAs($user)->post(route('qa-board.unresolve', $thread));
        // Assert
        $response->assertForbidden();
        $this->assertSame(QaThreadStatus::Unresolved, $thread->fresh()->status);
    }
}
