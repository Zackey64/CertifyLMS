<?php

namespace Tests\Feature\Http\EnrollmentGoal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Tests\TestCase;

class MarkAchievedTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は目標を達成済みにできる
    public function test_owner_can_goal_as_achieved(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
        ]);
        $goal = EnrollmentGoal::factory()->create([
            'enrollment_id' => $enrollment->id,
        ]);
        // Act
        $response = $this->actingAs($user)->post(route('enrollment-goals.markAchieved', $goal));
        // Assert
        $response->assertRedirect(route('enrollments.show', $enrollment));
        $this->assertDatabaseHas('enrollment_goals', [
            'id' => $goal->id,
            'achieved_at' => now(),
        ]);
    }

    // 作成者以外は目標を達成済みにできない
    public function test_not_owner_cannot_goal_as_achieved(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $otherUser = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
        ]);
        $goal = EnrollmentGoal::factory()->create([
            'enrollment_id' => $enrollment->id,
        ]);
        // Act
        $response = $this->actingAs($otherUser)->post(route('enrollment-goals.markAchieved', $goal));
        // Assert
        $response->assertForbidden();
    }
}
