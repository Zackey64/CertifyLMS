<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はプラン作成フォームを表示できない
    public function test_student_cannot_create_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.plans.create'));
        // Assert
        $response->assertForbidden();
    }

    // コーチはプラン作成フォームを表示できない
    public function test_coach_cannot_create_plan(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('admin.plans.create'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者はプラン作成フォームを表示できる
    public function test_admin_can_create_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.plans.create'));
        // Assert
        $response->assertOk();
    }
}
