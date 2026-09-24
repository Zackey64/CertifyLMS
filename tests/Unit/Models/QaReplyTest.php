<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QaReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relation_returns_reply_author(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $reply = QaReply::factory()
            ->for($user)
            ->create();
        // Act
        $author = $reply->user;
        // Assert
        $this->assertTrue($author->is($user));
    }

    public function test_thread_relation_returns_parent_thread(): void
    {
        // Arrange
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->create([
            'qa_thread_id' => $thread->id,
        ]);
        // Act
        $parent = $reply->thread;
        // Assert
        $this->assertTrue($parent->is($thread));
    }
}
