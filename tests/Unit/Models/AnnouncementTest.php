<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AnnouncementTargetType;
use App\Models\Announcement;
use App\Models\Certification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    // user_idカラムのUserモデルへのリレーション（BelongsTo）
    public function test_user_relation_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $announcement = Announcement::factory()->create([
            'user_id' => $user->id,
        ]);
        // Act
        $owner = $announcement->createdBy;
        // Assert
        $this->assertTrue($owner->is($user));
    }

    // target_certification_idカラムのCertificationモデルへのリレーション（BelongsTo）
    public function test_target_certification_relation_certification(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $announcement = Announcement::factory()->create([
            'target_certification_id' => $certification->id,
        ]);
        // Act
        $owner = $announcement->targetCertification;
        // Assert
        $this->assertTrue($owner->is($certification));
    }

    // target_user_idカラムのUserモデルへのリレーション（BelongsTo）
    public function test_target_user_relation_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $announcement = Announcement::factory()->create([
            'target_user_id' => $user->id,
        ]);
        // Act
        $owner = $announcement->targetUser;
        // Assert
        $this->assertTrue($owner->is($user));
    }

    // target_dateカラムの列挙型へのキャスト（enum）
    public function test_target_type_cast_enum(): void
    {
        // Arrange
        $announcement = Announcement::factory()->create(['target_type' => AnnouncementTargetType::AllStudents]);
        // Act
        $targetType = $announcement->target_type;
        // Assert
        $this->assertInstanceOf(AnnouncementTargetType::class, $targetType);
    }

    // dispatched_atカラムの列挙型へのキャスト（datetime）
    public function test_dispatched_at_cast_datetime(): void
    {
        // Arrange
        $announcement = Announcement::factory()->create(['dispatched_at' => '2024-06-01 12:34:56']);
        // Act
        $dispatchedAt = $announcement->dispatched_at;
        // Assert
        $this->assertInstanceOf(Carbon::class, $dispatchedAt);
    }
}
