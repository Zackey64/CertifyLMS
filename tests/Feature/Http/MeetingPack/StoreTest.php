<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は面談パック作成できない
    public function test_student_cannot_store_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $response = $this->actingAs($student)->post(route('admin.meeting-packs.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // コーチは面談パック作成できない
    public function test_coach_cannot_store_meeting_pack(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('admin.meeting-packs.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者は面談パックを作成できる
    public function test_admin_can_store_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('admin.meeting-packs.store'), $data);
        // Assert
        $meetingPack = MeetingPack::query()->where('name', 'テスト名前')->firstOrFail();
        $response->assertRedirect(route('admin.meeting-packs.show', $meetingPack));
    }
}
