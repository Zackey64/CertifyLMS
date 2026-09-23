<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\QaThread;
use App\Models\User;

class QaThreadPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::Student;
    }

    public function update(User $user, QaThread $thread): bool
    {
        return $user->id === $thread->user_id;
    }

    public function delete(User $user, QaThread $thread): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->id === $thread->user_id && $thread->replies()->count() === 0;
    }

    public function resolve(User $user, QaThread $thread): bool
    {
        return $user->id === $thread->user_id;
    }

    public function unresolve(User $user, QaThread $thread): bool
    {
        return $user->id === $thread->user_id;
    }
}
