<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\QaReply;
use App\Models\User;
use App\Policies\QaReplyPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QaReplyPolicyTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は回答を作成できる
    public function test_student_can_create_reply(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $reply = QaReply::factory()->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertTrue($policy->create($student, $reply));
    }

    // コーチは回答を作成できる
    public function test_coach_can_create_reply(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $reply = QaReply::factory()->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertTrue($policy->create($coach, $reply));
    }

    // 管理者は回答を作成できない
    public function test_admin_cannot_create_reply(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $reply = QaReply::factory()->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertFalse($policy->create($admin, $reply));
    }

    // 自分の回答を更新できる
    public function test_owner_can_update_reply(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $reply = QaReply::factory()->for($owner)->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertTrue($policy->update($owner, $reply));
    }

    // 他人の回答を更新できない
    public function test_non_owner_cannot_update_reply(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $reply = QaReply::factory()->for($owner)->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertFalse($policy->update($otherUser, $reply));
    }

    // 自分の回答は削除できる
    public function test_owner_can_delete_reply(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $reply = QaReply::factory()->for($user)->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertTrue($policy->delete($user, $reply));
    }

    // 他人の回答を削除できない
    public function test_non_owner_cannot_delete_reply(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $reply = QaReply::factory()->for($owner)->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertFalse($policy->delete($otherUser, $reply));
    }

    // 管理者は他人の回答を削除できる
    public function test_admin_can_delete_reply(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $reply = QaReply::factory()->for($student)->create();
        $policy = new QaReplyPolicy;
        // Assert
        $this->assertTrue($policy->delete($admin, $reply));
    }
}
