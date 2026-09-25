<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプラン詳細画面を表示できない
    public function test_student_cannot_view_plan_show(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $plan = Plan::factory()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.plans.show', $plan));
        // Assert
        $response->assertForbidden();
    }

    // コーチはプラン詳細画面を表示できない
    public function test_coach_cannot_view_plan_show(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $plan = Plan::factory()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('admin.plans.show', $plan));
        // Assert
        $response->assertForbidden();
    }

    // 管理者はプラン詳細画面を表示できる
    public function test_admin_can_view_plan_show(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.plans.show', $plan));
        // Assert
        $response->assertOk();
    }
}
