<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は面談パック更新できない
    public function test_student_cannot_update_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $pack = MeetingPack::factory()->create();
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $response = $this->actingAs($student)->patch(route('admin.meeting-packs.update', $pack), $data);
        // Assert
        $response->assertForbidden();
    }

    // コーチは面談パック更新できない
    public function test_coach_cannot_update_meeting_pack(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $pack = MeetingPack::factory()->create();
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $response = $this->actingAs($coach)->patch(route('admin.meeting-packs.update', $pack), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者は面談パックを更新できる
    public function test_admin_can_update_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create();
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $response = $this->actingAs($admin)->patch(route('admin.meeting-packs.update', $pack), $data);
        // Assert
        $response->assertRedirect(route('admin.meeting-packs.show', $pack));
        $this->assertDatabaseHas('meeting_packs', ['name' => 'テスト名前']);
    }
}
