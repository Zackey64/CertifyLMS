<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Enums\QaThreadStatus;
use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use App\Policies\QaThreadPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QaThreadPolicyTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は自分のスレッドを作成できる
    public function test_student_can_create_thread(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertTrue($policy->create($student));
    }

    // コーチはスレッドを作成できない
    public function test_coach_cannot_create_thread(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertFalse($policy->create($coach));
    }

    // 管理者はスレッドを作成できない
    public function test_admin_cannot_create_thread(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertFalse($policy->create($admin));
    }

    // 自分のスレッドを更新できる
    public function test_owner_can_update_thread(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->for($student)->create();
        $policy = new QaThreadPolicy;

        // Assert
        $this->assertTrue($policy->update($student, $thread));
    }

    // 他人のスレッドを更新できない
    public function test_non_owner_cannot_update_thread(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->for($owner)->create();
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertFalse($policy->update($otherUser, $thread));
    }

    // 自分のスレッドに回答がない場合は削除できる
    public function test_owner_can_delete_thread_without_replies(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $thread = QaThread::factory()->for($user)->create();
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertTrue($policy->delete($user, $thread));
    }

    // 自分のスレッドに回答がある場合は削除できない
    public function test_owner_cannot_delete_thread_with_replies(): void
    {
        // Arrange
        $user = User::factory()->student()->create();

        $thread = QaThread::factory()->for($user)->create();
        $policy = new QaThreadPolicy;
        QaReply::factory()->for($thread, 'thread')->create([
            'user_id' => $user->id,
        ]);
        // Assert
        $this->assertFalse($policy->delete($user, $thread));
    }

    // 他人のスレッドを削除できない
    public function test_non_owner_cannot_delete_thread(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->for($owner)->create();
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertFalse($policy->delete($otherUser, $thread));
    }

    // 管理者は他人のスレッドを削除できる
    public function test_admin_can_delete_thread(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->for($student)->create();
        $policy = new QaThreadPolicy;

        // Assert
        $this->assertTrue($policy->delete($admin, $thread));
    }

    // 自分のスレッドを解決済にできる
    public function test_owner_can_resolve_thread(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->for($student)->create();
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertTrue($policy->resolve($student, $thread));
    }

    // 他人のスレッドを解決済にできない
    public function test_non_owner_cannot_resolve_thread(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->for($owner)->create();
        $policy = new QaThreadPolicy;  
        // Assert
        $this->assertFalse($policy->resolve($otherUser, $thread));
    }

    // 自分のスレッドの解決済を解除できる
    public function test_owner_can_unresolve_thread(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->for($student)->create([
            'status' => QaThreadStatus::Resolved,
            'resolved_at' => now(),
        ]);
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertTrue($policy->unresolve($student, $thread));
    }

    // 他人のスレッドの解決済を解除できない
    public function test_non_owner_cannot_unresolve_thread(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $thread = QaThread::factory()->for($owner)->create([
            'status' => QaThreadStatus::Resolved,
            'resolved_at' => now(),
        ]);
        $policy = new QaThreadPolicy;
        // Assert
        $this->assertFalse($policy->unresolve($otherUser, $thread));
    }
}
