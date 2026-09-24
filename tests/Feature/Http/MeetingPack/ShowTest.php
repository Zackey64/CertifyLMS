<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は面談パック詳細画面を表示できない
    public function test_student_cannot_view_meeting_pack_show(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $pack = MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.meeting-packs.show', $pack));
        // Assert
        $response->assertForbidden();
    }

    // コーチは面談パック詳細画面を表示できない
    public function test_coach_cannot_view_meeting_pack_show(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $pack = MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('admin.meeting-packs.show', $pack));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は面談パック詳細画面を表示できる
    public function test_admin_can_view_meeting_pack_show(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.meeting-packs.show', $pack));
        // Assert
        $response->assertOk();
    }
}
