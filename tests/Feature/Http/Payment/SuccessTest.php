<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Payment;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuccessTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は決済完了画面を表示できる
    public function test_student_can_view_payment_success(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $payment = Payment::factory()->create([
            'user_id' => $student->id,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);
        $data = [
            'session_id' => $payment->stripe_checkout_session_id,
        ];
        // Act
        $response = $this->actingAs($student)->get(route('meeting-quota.success', $data));
        // Assert
        $response->assertOk();
    }

    // コーチは決済完了画面を表示できない
    public function test_coach_cannot_view_payment_success(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $payment = Payment::factory()->create([
            'user_id' => $coach->id,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);
        $data = [
            'session_id' => $payment->stripe_checkout_session_id,
        ];
        // Act
        $response = $this->actingAs($coach)->get(route('meeting-quota.success', $data));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は決済完了画面を表示できない
    public function test_admin_cannot_view_payment_success(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->create([
            'user_id' => $admin->id,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);
        $data = [
            'session_id' => $payment->stripe_checkout_session_id,
        ];
        // Act
        $response = $this->actingAs($admin)->get(route('meeting-quota.success', $data));
        // Assert
        $response->assertForbidden();
    }
}
