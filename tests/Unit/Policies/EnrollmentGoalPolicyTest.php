<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use App\Policies\EnrollmentGoalPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * EnrollmentGoalPolicy の判定を検証する Unit テスト。
 */
class EnrollmentGoalPolicyTest extends TestCase
{
    use RefreshDatabase;

    // 本人の受講登録に対して作成できる
    public function test_owner_can_create_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->create($owner, $enrollment);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は作成できない
    public function test_non_owner_cannot_create_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->create($nonOwner, $enrollment);
        // Assert
        $this->assertFalse($result);
    }

    // 本人は更新できる
    public function test_owner_can_update_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->update($owner, $goal);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は更新できない
    public function test_non_owner_cannot_update_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->update($nonOwner, $goal);
        // Assert
        $this->assertFalse($result);
    }

    // 本人は削除できる
    public function test_owner_can_delete_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->delete($owner, $goal);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は削除できない
    public function test_non_owner_cannot_delete_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->delete($nonOwner, $goal);
        // Assert
        $this->assertFalse($result);
    }

    // 本人は達成済にできる
    public function test_owner_can_achieve_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->markAchieved($owner, $goal);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は達成済にできない
    public function test_non_owner_cannot_achieve_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->markAchieved($nonOwner, $goal);
        // Assert
        $this->assertFalse($result);
    }

    // 本人は未達成にできる
    public function test_owner_can_unachieve_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->unmarkAchieved($owner, $goal);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は未達成にできない
    public function test_non_owner_cannot_unachieve_goal(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->for($owner)->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        $policy = new EnrollmentGoalPolicy;
        // Act
        $result = $policy->unmarkAchieved($nonOwner, $goal);
        // Assert
        $this->assertFalse($result);
    }
}
