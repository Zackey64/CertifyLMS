<?php

declare(strict_types=1);

namespace App\UseCases\Certificate;

use App\Enums\EnrollmentStatus;
use App\Exceptions\Certification\CertificateAlreadyIssuedException;
use App\Exceptions\Certification\EnrollmentNotPassedException;
use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 修了証を発行するユースケース。受講生自己発火型の修了処理 `\App\UseCases\Enrollment\ReceiveCertificateAction` から呼び出される。
 *
 * 業務分岐:
 * - Enrollment が `status=passed` + `passed_at != null` でない: EnrollmentNotPassedException（409）
 * - 同一 Enrollment に対する二重呼出: CertificateAlreadyIssuedException（409、事前 lockForUpdate + exists で検出）
 *
 * 修了証レコードの INSERT は `DB::transaction()` 内で実行する。
 */
final class IssueAction
{
    public function __construct(
        private readonly GeneratePdfAction $generatePdf,
    ) {}

    /**
     * @throws EnrollmentNotPassedException 受講登録が修了状態ではない
     * @throws CertificateAlreadyIssuedException 同一 Enrollment で修了証が既発行
     */
    public function __invoke(Enrollment $enrollment): Certificate
    {
        if ($enrollment->status !== EnrollmentStatus::Passed || $enrollment->passed_at === null) {
            throw new EnrollmentNotPassedException;
        }

        $path = null;

        try {
            return DB::transaction(function () use ($enrollment, &$path) {
                $existing = Certificate::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null) {
                    throw new CertificateAlreadyIssuedException;
                }

                $path = 'certificates/'.Str::ulid().'.pdf';

                $certificate = Certificate::create([
                    'user_id' => $enrollment->user_id,
                    'enrollment_id' => $enrollment->id,
                    'certification_id' => $enrollment->certification_id,
                    'pdf_path' => $path,
                    'issued_at' => now(),
                ]);

                ($this->generatePdf)($certificate, $path);

                return $certificate->refresh();
            });
        } catch (\Throwable $e) {
            if ($path !== null) {
                Storage::disk('private')->delete($path);
            }
            throw $e;
        }
    }
}
