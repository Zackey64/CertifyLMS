<?php

namespace Tests\Feature\Http\EnrollmentGoal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    // 作成者は目標を編集できる
    public function test_owner_can_edit_goal(): void
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
        $response = $this->actingAs($user)->get(route('enrollment-goals.edit', $goal));
        // Assert
        $response->assertStatus(200);
    }

    // 作成者以外は目標を編集できない
    public function test_not_owner_cannot_edit_goal(): void
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
        $response = $this->actingAs($otherUser)->get(route('enrollment-goals.edit', $goal));
        // Assert
        $response->assertForbidden();
    }
}
