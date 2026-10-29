<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 全件既読はログインユーザー自身の通知だけに適用される。
     */
    public function test_user_can_mark_all_own_notifications_as_read(): void
    {
        $user = User::factory()->student()->create();

        $otherUser = User::factory()->student()->create();

        $notification1 = $this->createNotification($user);
        $notification2 = $this->createNotification($user);
        $otherNotification = $this->createNotification($otherUser);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/notifications'.'/read-all')
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull(
            $notification1->fresh()->read_at
        );

        $this->assertNotNull(
            $notification2->fresh()->read_at
        );

        $this->assertNull(
            $otherNotification->fresh()->read_at
        );
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
