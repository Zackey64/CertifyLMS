<?php

declare(strict_types=1);

namespace Tests\Feature\Http\PaymentController;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は決済選択画面を表示できる
    public function test_student_can_view_payment_selection(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($student)->get(route('meeting-quota.checkout.select'));
        // Assert
        $response->assertOk();
    }

    // コーチは決済選択画面を表示できない
    public function test_coach_cannot_view_payment_selection(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('meeting-quota.checkout.select'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は決済選択画面を表示できない
    public function test_admin_cannot_view_payment_selection(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('meeting-quota.checkout.select'));
        // Assert
        $response->assertForbidden();
    }
}
