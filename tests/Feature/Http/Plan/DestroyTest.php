<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプランを削除できない
    public function test_student_cannot_delete_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $plan = Plan::factory()->create();
        // Act
        $response = $this->actingAs($student)->delete(route('admin.plans.destroy', $plan));
        // Assert
        $response->assertForbidden();
    }

    // コーチはプランを削除できない
    public function test_coach_cannot_delete_plan(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $plan = Plan::factory()->create();
        // Act
        $response = $this->actingAs($coach)->delete(route('admin.plans.destroy', $plan));
        // Assert
        $response->assertForbidden();
    }

    // 管理者はプランを削除できる
    public function test_admin_can_delete_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();
        // Act
        $response = $this->actingAs($admin)->delete(route('admin.plans.destroy', $plan));
        // Assert
        $response->assertRedirect(route('admin.plans.index'));
        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }
}
