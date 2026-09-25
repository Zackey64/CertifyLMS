<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプランをアーカイブ済にできない
    public function test_student_cannot_archive_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Published,
        ]);

        // Act
        $response = $this->actingAs($student)->post(route('admin.plans.archive', $plan));
        // Assert
        $response->assertForbidden();
        $this->assertSame(PlanStatus::Published, $plan->fresh()->status);
    }

    // コーチはプランをアーカイブ済にできない
    public function test_coach_cannot_archive_plan(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Published,
        ]);
        // Act
        $response = $this->actingAs($coach)->post(route('admin.plans.archive', $plan));
        // Assert
        $response->assertForbidden();
        $this->assertSame(PlanStatus::Published, $plan->fresh()->status);
    }

    // 管理者はプランをアーカイブ済にできる
    public function test_admin_can_archive_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Published,
        ]);
        // Act
        $response = $this->actingAs($admin)->post(route('admin.plans.archive', $plan));
        // Assert
        $response->assertRedirect(route('admin.plans.show', $plan));
        $this->assertSame(PlanStatus::Archived, $plan->fresh()->status);
    }
}
