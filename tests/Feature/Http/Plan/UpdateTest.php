<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプラン更新できない
    public function test_student_cannot_update_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $plan = Plan::factory()->create();
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $response = $this->actingAs($student)->put(route('admin.plans.update', $plan), $data);
        // Assert
        $response->assertForbidden();
    }

    // コーチはプラン更新できない
    public function test_coach_cannot_update_plan(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $plan = Plan::factory()->create();
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $response = $this->actingAs($coach)->put(route('admin.plans.update', $plan), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者はプランを更新できる
    public function test_admin_can_update_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $response = $this->actingAs($admin)->put(route('admin.plans.update', $plan), $data);
        // Assert
        $response->assertRedirect(route('admin.plans.show', $plan));
        $this->assertDatabaseHas('plans', ['name' => 'テスト名前']);
    }
}
