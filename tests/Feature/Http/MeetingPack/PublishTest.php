<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は面談パックを公開済にできない
    public function test_student_cannot_publish_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Draft,
        ]);

        // Act
        $response = $this->actingAs($student)->post(route('admin.meeting-packs.publish', $pack));
        // Assert
        $response->assertForbidden();
        $this->assertSame(MeetingPackStatus::Draft, $pack->fresh()->status);
    }

    // コーチは面談パックを公開済にできない
    public function test_coach_cannot_publish_meeting_pack(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Draft,
        ]);
        // Act
        $response = $this->actingAs($coach)->post(route('admin.meeting-packs.publish', $pack));
        // Assert
        $response->assertForbidden();
        $this->assertSame(MeetingPackStatus::Draft, $pack->fresh()->status);
    }

    // 管理者は面談パックを公開済にできる
    public function test_admin_can_publish_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Draft,
        ]);
        // Act
        $response = $this->actingAs($admin)->post(route('admin.meeting-packs.publish', $pack));
        // Assert
        $response->assertRedirect(route('admin.meeting-packs.show', $pack));
        $this->assertSame(MeetingPackStatus::Published, $pack->fresh()->status);
    }
}
