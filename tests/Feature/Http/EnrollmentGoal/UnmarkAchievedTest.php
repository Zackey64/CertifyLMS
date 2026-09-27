<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentGoal;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnmarkAchievedTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は目標を未達成にできる
    public function test_owner_can_goal_as_unachieved(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
        ]);
        $goal = EnrollmentGoal::factory()->create([
            'enrollment_id' => $enrollment->id,
            'achieved_at' => now(),
        ]);
        // Act
        $response = $this->actingAs($user)->delete(route('enrollment-goals.unmarkAchieved', $goal));
        // Assert
        $response->assertRedirect(route('enrollments.show', $enrollment));
        $this->assertDatabaseHas('enrollment_goals', [
            'id' => $goal->id,
            'achieved_at' => null,
        ]);
    }

    // 作成者以外は目標を未達成にできない
    public function test_not_owner_cannot_goal_as_unachieved(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
        ]);
        $goal = EnrollmentGoal::factory()->create([
            'enrollment_id' => $enrollment->id,
            'achieved_at' => now(),
        ]);
        // Act
        $response = $this->actingAs($otherUser)->delete(route('enrollment-goals.unmarkAchieved', $goal));
        // Assert
        $response->assertForbidden();
    }
}
