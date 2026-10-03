<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Announcement;

use App\Enums\AnnouncementTargetType;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生は管理者お知らせを作成できない
    public function test_student_cannot_store_announcement(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,

        ];
        // Act
        $response = $this->actingAs($student)->post(route('admin.announcements.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // コーチは管理者お知らせを作成できない
    public function test_coach_cannot_store_announcement(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('admin.announcements.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者は管理者お知らせを作成できる
    public function test_admin_can_store_announcement(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), $data);
        // Assert
        $this->assertDatabaseHas('announcements', [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ]);
        $announcement = Announcement::where('title', 'テストタイトル')->first();
        $response->assertRedirect(route('admin.announcements.show', $announcement));
    }
}
