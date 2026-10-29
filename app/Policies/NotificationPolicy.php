<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * 通知一覧の取得を許可する。
     */
    public function viewAny(User $user): bool
    {
        return $user->role !== UserRole::Admin;
    }

    /**
     * 個別通知の既読処理を許可する。
     */
    public function markAsRead(User $user, DatabaseNotification $notification): bool
    {
        return $user->role !== UserRole::Admin
            && (string) $notification->notifiable_id === (string) $user->getKey()
            && $notification->notifiable_type === $user->getMorphClass();
    }

    /**
     * 全件既読を許可する。
     */
    public function markAllAsRead(User $user): bool
    {
        return $user->role !== UserRole::Admin;
    }
}
