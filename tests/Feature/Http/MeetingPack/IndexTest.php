<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は面談パック一覧画面を表示できない
    public function test_student_cannot_view_meeting_pack_index(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('admin.meeting-packs.index'));
        // Assert
        $response->assertForbidden();
    }

    // コーチは面談パック一覧画面を表示できない
    public function test_coach_cannot_view_meeting_pack_index(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();

        // Act
        $response = $this->actingAs($coach)->get(route('admin.meeting-packs.index'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は面談パック一覧画面を表示できる
    public function test_admin_can_view_meeting_pack_index(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.meeting-packs.index'));
        // Assert
        $response->assertOk();
    }

    public function test_status_filter(): void
    {
        // Arrange
        $user = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'name' => 'MatchThread',
            'status' => MeetingPackStatus::Published->value,
        ]);
        MeetingPack::factory()->create([
            'name' => 'OtherThread',
            'status' => MeetingPackStatus::Draft->value,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('admin.meeting-packs.index', ['status' => $pack->status]));
        // Assert
        $response->assertOk();
        $response->assertSee('MatchThread');
        $response->assertDontSee('OtherThread');
    }

    public function test_keyword_filter(): void
    {
        // Arrange
        $user = User::factory()->admin()->create();
        MeetingPack::factory()->create([
            'name' => 'MatchThread',
        ]);
        MeetingPack::factory()->create([
            'name' => 'OtherThread',
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('admin.meeting-packs.index', ['keyword' => 'Match']));
        // Assert
        $response->assertOk();
        $response->assertSee('MatchThread');
        $response->assertDontSee('OtherThread');
    }
}
