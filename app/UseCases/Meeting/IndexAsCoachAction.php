<?php

declare(strict_types=1);

namespace App\UseCases\Meeting;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IndexAsCoachAction
{
    public function __invoke(
        User $coach,
        ?string $filter,
        ?string $studentId,
        ?string $enrollmentId,
    ): LengthAwarePaginator {
        $query = Meeting::query()
            ->with([
                'enrollment.certification',
                'student',
            ])
            ->forCoach($coach);

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        if ($enrollmentId !== null) {
            $query->where('enrollment_id', $enrollmentId);
        }

        match ($filter) {
            'upcoming' => $query->upcoming()->orderBy('scheduled_at'),
            'past' => $query->past()->orderByDesc('scheduled_at'),
            default => $query->orderByDesc('scheduled_at'),
        };

        return $query->paginate(20);
    }
}
