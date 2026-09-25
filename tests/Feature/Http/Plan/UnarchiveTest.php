<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnarchiveTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプランを下書きに戻せない
    public function test_student_cannot_unarchive_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Archived,
        ]);

        // Act
        $response = $this->actingAs($student)->post(route('admin.plans.unarchive', $plan));
        // Assert
        $response->assertForbidden();
        $this->assertSame(PlanStatus::Archived, $plan->fresh()->status);
    }

    // コーチはプランを下書きに戻せない
    public function test_coach_cannot_unarchive_plan(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Archived,
        ]);
        // Act
        $response = $this->actingAs($coach)->post(route('admin.plans.unarchive', $plan));
        // Assert
        $response->assertForbidden();
        $this->assertSame(PlanStatus::Archived, $plan->fresh()->status);
    }

    // 管理者はプランを下書きに戻せる
    public function test_admin_can_unarchive_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Archived,
        ]);
        // Act
        $response = $this->actingAs($admin)->post(route('admin.plans.unarchive', $plan));
        // Assert
        $response->assertRedirect(route('admin.plans.show', $plan));
        $this->assertSame(PlanStatus::Draft, $plan->fresh()->status);
    }
}
