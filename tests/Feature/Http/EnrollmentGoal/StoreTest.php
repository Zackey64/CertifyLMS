<?php

namespace Tests\Feature\Http\EnrollmentGoal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Enrollment;
use App\Models\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生かつ受講中のコースに対して目標を登録できる
    public function test_student_can_store_goal(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $student->id,
        ]);
        $data = [
            'title' => '新規目標',
        ];
        // Act
        $response = $this->actingAs($student)->post(route('enrollments.goals.store', $enrollment), $data);
        // Assert
        $this->assertDatabaseHas('enrollment_goals', [
            'enrollment_id' => $enrollment->id,
            'title' => '新規目標',
        ]);
        $response->assertRedirect(route('enrollments.show', $enrollment));
    }
    // 他の受講生は目標を登録できない
    public function test_other_student_cannot_store_goal(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $student->id,
        ]);
        $data = [
            'title' => '新規目標',
        ];
        // Act
        $response = $this->actingAs($otherStudent)->post(route('enrollments.goals.store', $enrollment), $data);
        // Assert
        $response->assertForbidden();
    }
}
