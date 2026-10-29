<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 通知一覧にはログインユーザー自身の通知だけが表示される。
     */
    public function test_user_can_only_view_own_notifications(): void
    {
        $user = User::factory()->student()->create();

        $otherUser = User::factory()->student()->create();

        $notification = $this->createNotification($user);
        $this->createNotification($otherUser);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $notification->id)
            ->assertJsonPath('unread_count', 1);
    }

    /**
     * テスト用のデータベース通知を作成する。
     */
    private function createNotification(
        User $user,
    ): DatabaseNotification {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test_notification',
            'data' => [
                'notification_type' => 'test',
                'title' => 'テスト通知',
                'body' => 'テスト通知の本文です。',
                'url' => '/notifications',
            ],
            'read_at' => null,
        ]);
    }
}
