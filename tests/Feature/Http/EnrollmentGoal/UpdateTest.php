<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentGoal;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は目標を更新できる
    public function test_owner_can_update_goal(): void
    {
        // Arrange
        $user = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
        ]);
        $goal = EnrollmentGoal::factory()->create([
            'enrollment_id' => $enrollment->id,
        ]);

        $data = [
            'title' => '更新後',
        ];
        // Act
        $response = $this->actingAs($user)->patch(route('enrollment-goals.update', $goal), $data);
        // Assert
        $response->assertRedirect(route('enrollments.show', $enrollment));
        $this->assertDatabaseHas('enrollment_goals', [
            'title' => '更新後',
        ]);
    }

    // 作成者以外は目標を更新できない
    public function test_not_owner_cannot_update_goal(): void
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

        $data = [
            'title' => '更新後',
        ];
        // Act
        $response = $this->actingAs($otherUser)->patch(route('enrollment-goals.update', $goal), $data);
        // Assert
        $response->assertForbidden();
    }
}
