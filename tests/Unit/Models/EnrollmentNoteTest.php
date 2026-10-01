<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Enrollment;
use App\Models\EnrollmentNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentNoteTest extends TestCase
{
    use RefreshDatabase;

    // enrollment_idカラムのEnrollmentモデルへのリレーション（BelongsTo）
    public function test_enrollment_id_relation_enrollment(): void
    {
        // Arrange
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->for($enrollment)->create();
        // Act
        $owner = $note->enrollment;
        // Assert
        $this->assertTrue($owner->is($enrollment));
    }

    // user_idカラムのUserモデルへのリレーション（BelongsTo）
    public function test_user_id_relation_user(): void
    {
        // Arrange
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->for($enrollment)->create();
        $user = $note->author;
        // Act
        $owner = $note->author;
        // Assert
        $this->assertTrue($user->is($owner));
    }
}
