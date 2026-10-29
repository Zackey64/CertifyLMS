<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarkAsReadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザーは自分の通知を既読にできる。
     */
    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->student()->create();

        $notification = $this->createNotification($user);

        Sanctum::actingAs($user);

        $this->postJson(
            '/api/v1/notifications'."/{$notification->id}/read"
        )
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath(
                'data.read_at',
                fn ($value) => $value !== null,
            );

        $this->assertNotNull(
            $notification->fresh()->read_at
        );
    }

    /**
     * ユーザーは他人の通知を既読にできない。
     */
    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $user = User::factory()->student()->create();

        $otherUser = User::factory()->student()->create();

        $notification = $this->createNotification($otherUser);

        Sanctum::actingAs($user);

        $this->postJson(
            '/api/v1/notifications'."/{$notification->id}/read"
        )->assertNotFound();

        $this->assertNull(
            $notification->fresh()->read_at
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
