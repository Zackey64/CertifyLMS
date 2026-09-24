<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QaThreadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relation_returns_thread_author(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->for($user)->create();
        // Act
        $author = $thread->user;
        // Assert
        $this->assertTrue($author->is($user));
    }

    public function test_certification_relation_returns_parent_certification(): void
    {
        // Arrange
        $certification = Certification::factory()->published()->create();
        $thread = QaThread::factory()->for($certification)->create();
        // Act
        $parent = $thread->certification;
        // Assert
        $this->assertTrue($parent->is($certification));
    }

    public function test_replies_relation_returns_attached_replies(): void
    {
        // Arrange
        $thread = QaThread::factory()->create();
        QaReply::factory()->create([
            'qa_thread_id' => $thread->id,
        ]);
        QaReply::factory()->create([
            'qa_thread_id' => $thread->id,
        ]);
        QaReply::factory()->create();
        // Act
        $replies = $thread->replies;
        // Assert
        $this->assertCount(2, $replies);
    }

    public function test_status_cast_returns_enum(): void
    {
        // Arrange
        $thread = QaThread::factory()->create();
        // Act
        $fresh = $thread->fresh();
        // Assert
        $this->assertInstanceOf(QaThreadStatus::class, $fresh->status);
    }

    public function test_resolved_at_cast_returns_carbon(): void
    {
        // Arrange
        $thread = QaThread::factory()->create([
            'status' => QaThreadStatus::Resolved,
            'resolved_at' => now(),
        ]);
        // Act
        $fresh = $thread->fresh();
        // Assert
        $this->assertInstanceOf(Carbon::class, $fresh->resolved_at);
    }
}
