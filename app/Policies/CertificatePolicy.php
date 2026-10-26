<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function download(User $user, Certificate $certificate): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        // 受講生：本人の修了証のみ
        if ($user->role === UserRole::Student) {
            return $certificate->user_id === $user->id;
        }

        // コーチ：担当資格の修了証のみ
        if ($user->role === UserRole::Coach) {
            return $certificate->certification
                ->coaches()->where('users.id', $user->id)->exists();
        }

        return false;
    }
}
