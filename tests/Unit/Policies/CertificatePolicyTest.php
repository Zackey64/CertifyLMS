<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Certification;
use App\Models\Enrollment;
use App\Models\User;
use App\Policies\CertificatePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificatePolicyTest extends TestCase
{
    use RefreshDatabase;

    // 受講生：本人は修了証をダウンロードできる
    public function test_student_owner_can_download_certificate(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $certificate = Certificate::factory()->for($owner)->create();
        $policy = new CertificatePolicy;
        // Act
        $result = $policy->download($owner, $certificate);
        // Assert
        $this->assertTrue($result);
    }

    // 受講生：本人以外は修了証をダウンロードできない
    public function test_student_non_owner_cannot_download_certificate(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $certificate = Certificate::factory()->for($owner)->create();
        $policy = new CertificatePolicy;
        // Act
        $result = $policy->download($nonOwner, $certificate);
        // Assert
        $this->assertFalse($result);
    }

    // コーチ：担当資格は修了証をダウンロードできる
    public function test_coach_can_download_certificate(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $owner = User::factory()->student()->create();
        $certification = Certification::factory()->create();
        $certification->coaches()->attach($coach->id, [
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
            'unassigned_at' => null,
        ]);
        $enrollment = Enrollment::factory()->for($owner)->for($certification)
            ->create([
                'status' => EnrollmentStatus::Passed->value,
                'passed_at' => now(),
            ]);
        $certificate = Certificate::factory()->for($owner)->forEnrollment($enrollment)->create();

        $policy = new CertificatePolicy;
        // Act
        $result = $policy->download($coach, $certificate);
        // Assert
        $this->assertTrue($result);
    }

    // 管理者：修了証をダウンロードできる
    public function test_admin_owner_can_download_certificate(): void
    {
        // Arrange
        $owner = User::factory()->admin()->create();
        $certificate = Certificate::factory()->create();
        $policy = new CertificatePolicy;
        // Act
        $result = $policy->download($owner, $certificate);
        // Assert
        $this->assertTrue($result);
    }
}
