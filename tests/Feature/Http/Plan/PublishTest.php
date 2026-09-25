<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプランを公開済にできない
    public function test_student_cannot_publish_meeting_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Draft,
        ]);

        // Act
        $response = $this->actingAs($student)->post(route('admin.plans.publish', $plan));
        // Assert
        $response->assertForbidden();
        $this->assertSame(PlanStatus::Draft, $plan->fresh()->status);
    }

    // コーチはプランを公開済にできない
    public function test_coach_cannot_publish_meeting_plan(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Draft,
        ]);
        // Act
        $response = $this->actingAs($coach)->post(route('admin.plans.publish', $plan));
        // Assert
        $response->assertForbidden();
        $this->assertSame(PlanStatus::Draft, $plan->fresh()->status);
    }

    // 管理者はプランを公開済にできる
    public function test_admin_can_publish_meeting_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Draft,
        ]);
        // Act
        $response = $this->actingAs($admin)->post(route('admin.plans.publish', $plan));
        // Assert
        $response->assertRedirect(route('admin.plans.show', $plan));
        $this->assertSame(PlanStatus::Published, $plan->fresh()->status);
    }
}
