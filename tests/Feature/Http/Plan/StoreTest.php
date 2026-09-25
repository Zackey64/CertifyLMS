<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプラン作成できない
    public function test_student_cannot_store_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $response = $this->actingAs($student)->post(route('admin.plans.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // コーチはプラン作成できない
    public function test_coach_cannot_store_plan(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('admin.plans.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者はプランを作成できる
    public function test_admin_can_store_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('admin.plans.store'), $data);
        // Assert
        $plan = Plan::query()->where('name', 'テスト名前')->firstOrFail();
        $response->assertRedirect(route('admin.plans.show', $plan));
    }
}
