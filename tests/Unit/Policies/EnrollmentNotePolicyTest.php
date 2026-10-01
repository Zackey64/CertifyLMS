<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Certification;
use App\Models\Enrollment;
use App\Models\EnrollmentNote;
use App\Models\User;
use App\Policies\EnrollmentNotePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentNotePolicyTest extends TestCase
{
    use RefreshDatabase;

    // 受講登録に対してコーチがメモを作成できる
    public function test_coach_can_create_note(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $student = User::factory()->student()->create();
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->create();
        $certification->coaches()->attach($coach->id, [
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);
        $enrollment = Enrollment::factory()->for($student)->for($certification)->create();

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->create($coach, $enrollment);
        // Assert
        $this->assertTrue($result);
    }

    // 受講登録に対して学生はメモを作成できない
    public function test_student_cannot_create_note(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $student = User::factory()->student()->create();
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->create();
        $certification->coaches()->attach($coach->id, [
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);
        $enrollment = Enrollment::factory()->for($student)->for($certification)->create();

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->create($student, $enrollment);
        // Assert
        $this->assertFalse($result);
    }

    // 受講登録に対して管理者はメモを作成できる
    public function test_admin_can_create_note(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $student = User::factory()->student()->create();
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->create();
        $certification->coaches()->attach($coach->id, [
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);
        $enrollment = Enrollment::factory()->for($student)->for($certification)->create();

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->create($admin, $enrollment);
        // Assert
        $this->assertTrue($result);
    }

    // コーチは自身のメモを更新できる
    public function test_coach_can_update_note(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->create([
            'user_id' => $coach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->update($coach, $note);
        // Assert
        $this->assertTrue($result);
    }

    // コーチは他人のメモを更新できない
    public function test_coach_cannot_update_other_note(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $otherCoach = User::factory()->coach()->create();
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->create([
            'user_id' => $otherCoach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->update($coach, $note);
        // Assert
        $this->assertFalse($result);
    }

    // 管理者は他人のメモを更新できる
    public function test_admin_can_update_other_note(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->create([
            'user_id' => $coach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->update($admin, $note);
        // Assert
        $this->assertTrue($result);
    }

    // コーチは自身のメモを削除できる
    public function test_coach_can_delete_note(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->create([
            'user_id' => $coach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->delete($coach, $note);
        // Assert
        $this->assertTrue($result);
    }

    // コーチは他人のメモを削除できない
    public function test_coach_cannot_delete_other_note(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $otherCoach = User::factory()->coach()->create();
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->create([
            'user_id' => $otherCoach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->delete($coach, $note);
        // Assert
        $this->assertFalse($result);
    }

    // 管理者は他人のメモを削除できる
    public function test_admin_can_delete_other_note(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $enrollment = Enrollment::factory()->create();
        $note = EnrollmentNote::factory()->create([
            'user_id' => $coach->id,
            'enrollment_id' => $enrollment->id,
        ]);

        $policy = new EnrollmentNotePolicy;
        // Act
        $result = $policy->delete($admin, $note);
        // Assert
        $this->assertTrue($result);
    }
}
