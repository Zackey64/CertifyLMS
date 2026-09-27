<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * EnrollmentGoal モデルのリレーション・Scope・Cast を検証する Unit テスト。
 */
class EnrollmentGoalTest extends TestCase
{
    use RefreshDatabase;

    // enrollment_idカラムのEnrollmentモデルへのリレーション（BelongsTo）
    public function test_enrollment_id_relation_enrollment(): void
    {
        // Arrange
        $enrollment = Enrollment::factory()->create();
        $goal = EnrollmentGoal::factory()->for($enrollment)->create();
        // Act
        $owner = $goal->enrollment;
        // Assert
        $this->assertTrue($owner->is($enrollment));
    }

    // target_dateカラムの列挙型へのキャスト（date）
    public function test_target_date_cast_date(): void
    {
        // Arrange
        $goal = EnrollmentGoal::factory()->create(['target_date' => '2024-06-01']);
        // Act
        $targetDate = $goal->target_date;
        // Assert
        $this->assertInstanceOf(Carbon::class, $targetDate);
    }

    // achieved_atカラムの列挙型へのキャスト（datetime）
    public function test_achieved_at_cast_datetime(): void
    {
        // Arrange
        $goal = EnrollmentGoal::factory()->create(['achieved_at' => '2024-06-01 12:34:56']);
        // Act
        $achievedAt = $goal->achieved_at;
        // Assert
        $this->assertInstanceOf(Carbon::class, $achievedAt);
    }
}
