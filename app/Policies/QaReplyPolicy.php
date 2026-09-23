<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\QaReply;
use App\Models\User;

class QaReplyPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::Student || $user->role === UserRole::Coach;
    }

    public function update(User $user, QaReply $reply): bool
    {
        return $user->id === $reply->user_id;
    }

    public function delete(User $user, QaReply $reply): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->id === $reply->user_id;
    }
}
