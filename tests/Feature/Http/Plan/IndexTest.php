<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプラン一覧画面を表示できない
    public function test_student_cannot_view_plan_index(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.plans.index'));
        // Assert
        $response->assertForbidden();
    }

    // コーチはプラン一覧画面を表示できない
    public function test_coach_cannot_view_plan_index(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();

        // Act
        $response = $this->actingAs($coach)->get(route('admin.plans.index'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者はプラン一覧画面を表示できる
    public function test_admin_can_view_plan_index(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.plans.index'));
        // Assert
        $response->assertOk();
    }

    public function test_status_filter(): void
    {
        // Arrange
        $user = User::factory()->admin()->create();
        $pack = Plan::factory()->create([
            'name' => 'MatchThread',
            'status' => PlanStatus::Published->value,
        ]);
        Plan::factory()->create([
            'name' => 'OtherThread',
            'status' => PlanStatus::Draft->value,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('admin.plans.index', ['status' => $pack->status]));
        // Assert
        $response->assertOk();
        $response->assertSee('MatchThread');
        $response->assertDontSee('OtherThread');
    }

    public function test_keyword_filter(): void
    {
        // Arrange
        $user = User::factory()->admin()->create();
        Plan::factory()->create([
            'name' => 'MatchThread',
        ]);
        Plan::factory()->create([
            'name' => 'OtherThread',
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('admin.plans.index', ['keyword' => 'Match']));
        // Assert
        $response->assertOk();
        $response->assertSee('MatchThread');
        $response->assertDontSee('OtherThread');
    }
}
