<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Certificate;

use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Certification;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    // 受講生：本人は修了証をダウンロードできる
    public function test_student_owner_can_download_certificate(): void
    {
        Storage::fake('private');
        // Arrange
        $student = User::factory()->student()->create();
        $certificate = Certificate::factory()
            ->for($student)
            ->create([
                'pdf_path' => 'certificates/test.pdf',
            ]);

        Storage::disk('private')->put(
            $certificate->pdf_path,
            '%PDF-1.4 test',
        );
        // Act
        $response = $this->actingAs($student)->get(route('certificates.download', $certificate));
        // Assert
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    // コーチ：担当資格の修了証をダウンロードできる
    public function test_coach_can_download_assigned_certificates(): void
    {
        Storage::fake('private');
        // Arrange
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $student = User::factory()->student()->create();

        $certification = Certification::factory()->create();
        $certification->coaches()->attach($coach->id, [
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
            'unassigned_at' => null,
        ]);

        $enrollment = Enrollment::factory()->for($student)->for($certification)
            ->create([
                'status' => EnrollmentStatus::Passed->value,
                'passed_at' => now(),
            ]);
        $certificate = Certificate::factory()->for($student)->forEnrollment($enrollment)
            ->create([
                'pdf_path' => 'certificates/test.pdf',
            ]);

        Storage::disk('private')->put(
            $certificate->pdf_path,
            '%PDF-1.4 test',
        );
        // Act
        $response = $this->actingAs($coach)->get(route('certificates.download', $certificate));
        // Assert
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    // 管理者：修了証をダウンロードできる
    public function test_admin_can_download_certificates(): void
    {
        Storage::fake('private');
        // Arrange
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();

        $certificate = Certificate::factory()->for($student)
            ->create([
                'pdf_path' => 'certificates/test.pdf',
            ]);

        Storage::disk('private')->put(
            $certificate->pdf_path,
            '%PDF-1.4 test',
        );
        // Act
        $response = $this->actingAs($admin)->get(route('certificates.download', $certificate));
        // Assert
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
