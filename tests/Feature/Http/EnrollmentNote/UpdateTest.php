<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentNote;

use App\Models\Certification;
use App\Models\Enrollment;
use App\Models\EnrollmentNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 作成者はメモを更新できる
    public function test_owner_can_update_note(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $student = User::factory()->student()->create();

        $certification = Certification::factory()->create();

        $certification->coaches()->attach($coach->id, [
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);
        $enrollment = Enrollment::factory()->create([
            'user_id' => $student->id,
            'certification_id' => $certification->id,
        ]);
        $note = EnrollmentNote::factory()->create([
            'user_id' => $coach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $data = [
            'body' => '更新後',
        ];
        // Act
        $response = $this->actingAs($coach)->patch(route('enrollment-notes.update', $note), $data);
        // Assert
        $response->assertRedirect(route('enrollments.show', $enrollment));
        $this->assertDatabaseHas('enrollment_notes', [
            'body' => '更新後',
        ]);
    }

    // 作成者以外はメモを更新できない
    public function test_not_owner_cannot_update_note(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $otherCoach = User::factory()->coach()->create();
        $student = User::factory()->student()->create();

        $certification = Certification::factory()->create();

        $certification->coaches()->attach($coach->id, [
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);
        $enrollment = Enrollment::factory()->create([
            'user_id' => $student->id,
            'certification_id' => $certification->id,
        ]);
        $note = EnrollmentNote::factory()->create([
            'user_id' => $coach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $data = [
            'body' => '更新後',
        ];
        // Act
        $response = $this->actingAs($otherCoach)->patch(route('enrollment-notes.update', $note), $data);
        // Assert
        $response->assertForbidden();
    }
}
