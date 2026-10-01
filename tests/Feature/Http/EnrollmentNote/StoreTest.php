<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentNote;

use App\Models\Certification;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // コーチかつ受講中のコースに対してメモを登録できる
    public function test_coach_can_store_note(): void
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
        $data = [
            'body' => '新規メモ',
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('enrollments.notes.store', $enrollment), $data);
        // Assert
        $this->assertDatabaseHas('enrollment_notes', [
            'enrollment_id' => $enrollment->id,
            'body' => '新規メモ',
        ]);
        $response->assertRedirect(route('enrollments.show', $enrollment));
    }

    // 他のコーチはメモを登録できない
    public function test_other_coach_cannot_store_note(): void
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
        $data = [
            'body' => '新規メモ',
        ];
        // Act
        $response = $this->actingAs($otherCoach)->post(route('enrollments.notes.store', $enrollment), $data);
        // Assert
        $response->assertForbidden();
    }
}
