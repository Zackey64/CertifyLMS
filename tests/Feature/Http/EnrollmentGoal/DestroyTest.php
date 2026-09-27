<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentGoal;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は目標を削除できる
    public function test_owner_can_destroy_goal(): void
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
        $response = $this->actingAs($user)->delete(route('enrollment-goals.destroy', $goal));
        // Assert
        $response->assertRedirect(route('enrollments.show', $enrollment));
        $this->assertDatabaseMissing('enrollment_goals', [
            'id' => $goal->id,
        ]);
    }

    // 作成者以外は目標を削除できない
    public function test_not_owner_cannot_destroy_goal(): void
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
        $response = $this->actingAs($otherUser)->delete(route('enrollment-goals.destroy', $goal));
        // Assert
        $response->assertForbidden();
    }
}
