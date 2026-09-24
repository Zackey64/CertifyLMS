<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Enums\MeetingPackStatus;
use App\Models\MeetingPack;
use App\Models\User;
use App\Policies\MeetingPackPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingPackTest extends TestCase
{
    use RefreshDatabase;

    // アドミンのみ面談パック一覧画面を閲覧できる
    public function test_admin_only_can_view_any_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->viewAny($student));
        $this->assertFalse($policy->viewAny($coach));
        $this->assertTrue($policy->viewAny($admin));
    }

    // アドミンのみ面談パックを作成できる
    public function test_admin_only_can_create_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->create($student));
        $this->assertFalse($policy->create($coach));
        $this->assertTrue($policy->create($admin));
    }

    // アドミンのみ面談パック詳細画面を閲覧できる
    public function test_admin_only_can_view_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create();
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->view($student, $pack));
        $this->assertFalse($policy->view($coach, $pack));
        $this->assertTrue($policy->view($admin, $pack));
    }

    // アドミンのみ面談パックを更新できる
    public function test_admin_only_can_update_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create();
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->update($student, $pack));
        $this->assertFalse($policy->update($coach, $pack));
        $this->assertTrue($policy->update($admin, $pack));
    }

    // アドミンのみ面談パックを削除できる
    public function test_admin_only_can_delete_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create();
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->delete($student, $pack));
        $this->assertFalse($policy->delete($coach, $pack));
        $this->assertTrue($policy->delete($admin, $pack));
    }

    // 公開中の面談パックは削除できない
    public function test_admin_cannot_delete_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Published,
        ]);
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->delete($admin, $pack));
    }

    // アドミンのみ面談パックを公開できる
    public function test_admin_only_can_publish_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Draft,
        ]);
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->publish($student, $pack));
        $this->assertFalse($policy->publish($coach, $pack));
        $this->assertTrue($policy->publish($admin, $pack));
    }

    // アーカイブの面談パックは公開できない
    public function test_admin_cannot_publish_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Archived,
        ]);
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->publish($admin, $pack));
    }

    // アドミンのみ面談パックをアーカイブできる
    public function test_admin_only_can_archive_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Published,
        ]);
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->archive($student, $pack));
        $this->assertFalse($policy->archive($coach, $pack));
        $this->assertTrue($policy->archive($admin, $pack));
    }

    // 下書きの面談パックはアーカイブできない
    public function test_admin_cannot_archive_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Draft,
        ]);
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->archive($admin, $pack));
    }

    // アドミンのみ面談パックを下書きに戻せる
    public function test_admin_only_can_unarchive_meeting_pack(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Archived,
        ]);
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->unarchive($student, $pack));
        $this->assertFalse($policy->unarchive($coach, $pack));
        $this->assertTrue($policy->unarchive($admin, $pack));
    }

    // 公開中の面談パックは下書きにできない
    public function test_admin_cannot_unarchive_meeting_pack(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $pack = MeetingPack::factory()->create([
            'status' => MeetingPackStatus::Published,
        ]);
        $policy = new MeetingPackPolicy;
        // Assert
        $this->assertFalse($policy->unarchive($admin, $pack));
    }
}
