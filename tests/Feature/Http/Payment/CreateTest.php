<?php

declare(strict_types=1);

namespace Tests\Feature\Http\PaymentController;

use App\Enums\MeetingPackStatus;
use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は決済できる
    public function test_student_can_create_payment(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $meetingPack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Published,
        ]);
        $data = [
            'meeting_pack_id' => $meetingPack->id,
        ];
        // Act
        $response = $this->actingAs($student)->post(route('meeting-quota.checkout.create'), $data);
        // Assert
        $response->assertRedirect();
    }

    // コーチは決済できない
    public function test_coach_cannot_create_payment(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $meetingPack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Published,
        ]);
        $data = [
            'meeting_pack_id' => $meetingPack->id,
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('meeting-quota.checkout.create'), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者は決済できない
    public function test_admin_cannot_create_payment(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $meetingPack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Published,
        ]);
        $data = [
            'meeting_pack_id' => $meetingPack->id,
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('meeting-quota.checkout.create'), $data);
        // Assert
        $response->assertForbidden();
    }
}
