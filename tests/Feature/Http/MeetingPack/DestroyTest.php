<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は面談パック削除できない
    public function test_student_cannot_delete_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $pack = MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($student)->delete(route('admin.meeting-packs.destroy', $pack));
        // Assert
        $response->assertForbidden();
    }

    // コーチは面談パック削除できない
    public function test_coach_cannot_delete_meeting_pack(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $pack = MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($coach)->delete(route('admin.meeting-packs.destroy', $pack));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は面談パックを削除できる
    public function test_admin_can_delete_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($admin)->delete(route('admin.meeting-packs.destroy', $pack));
        // Assert
        $response->assertRedirect(route('admin.meeting-packs.index'));
        $this->assertDatabaseMissing('meeting_packs', ['id' => $pack->id]);
    }
}
