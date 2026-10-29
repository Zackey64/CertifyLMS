<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証ユーザーは通知APIを利用できない。
     */
    public function test_guest_cannot_access_notification_api(): void
    {
        $user = User::factory()->student()->create();

        $notification = $this->createNotification($user);

        $this->getJson('/api/v1/notifications')
            ->assertUnauthorized();

        $this->postJson('/api/v1/notifications'.'/read-all')
            ->assertUnauthorized();

        $this->postJson(
            '/api/v1/notifications'."/{$notification->id}/read"
        )->assertUnauthorized();
    }

    /**
     * 管理者は通知APIを利用できない。
     */
    public function test_admin_cannot_access_notification_api(): void
    {
        $admin = User::factory()->admin()->create();

        $notification = $this->createNotification($admin);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/notifications')
            ->assertForbidden();

        $this->postJson('/api/v1/notifications'.'/read-all')
            ->assertForbidden();

        $this->postJson(
            '/api/v1/notifications'."/{$notification->id}/read"
        )->assertForbidden();

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
