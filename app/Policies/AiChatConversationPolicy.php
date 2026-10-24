<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AiChatConversation;
use App\Models\User;

class AiChatConversationPolicy
{
    /**
     * 会話を閲覧できるか。
     */
    public function view(User $user, AiChatConversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }

    /**
     * 会話を更新できるか。
     */
    public function update(User $user, AiChatConversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }

    /**
     * 会話を削除できるか。
     */
    public function delete(User $user, AiChatConversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }

    /**
     * メッセージをを追加できるか。
     */
    public function store(User $user, AiChatConversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }
}
