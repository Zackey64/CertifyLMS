<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ChatRoom;
use App\Models\QaThread;
use App\Models\User;
use App\Notifications\ChatMessageReceivedNotification;
use App\Notifications\MeetingCanceledNotification;
use App\Notifications\MeetingReservedNotification;
use App\Notifications\QaReplyReceivedNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::where('role', UserRole::Student->value)->orderBy('created_at')->first();
        $coach = User::where('role', UserRole::Coach->value)->orderBy('created_at')->first();
        $room = ChatRoom::first();
        $thread = QaThread::first();

        foreach ([$student, $coach] as $user) {
            if ($user === null) {
                continue;
            }

            for ($i = 0; $i < 8; $i++) {

                if ($room !== null) {
                    $notification = new ChatMessageReceivedNotification($room);
                    $user->notifications()->create([
                        'id' => (string) Str::uuid(),
                        'type' => ChatMessageReceivedNotification::class,
                        'data' => $notification->toDatabase($user),
                        'read_at' => fake()->boolean() ? now() : null,
                    ]);
                }

                if ($thread !== null) {
                    $notification = new QaReplyReceivedNotification($thread);
                    $user->notifications()->create([
                        'id' => (string) Str::uuid(),
                        'type' => QaReplyReceivedNotification::class,
                        'data' => $notification->toDatabase($user),
                        'read_at' => fake()->boolean() ? now() : null,
                    ]);
                }

                $this->createNotification(
                    $user,
                    MeetingReservedNotification::class,
                    [
                        'notification_type' => 'meeting_reserved',
                        'title' => '面談予約のお知らせ',
                        'body' => '面談が予約されました。',
                        'url' => '/meetings',
                    ],
                );

                $this->createNotification(
                    $user,
                    MeetingCanceledNotification::class,
                    [
                        'notification_type' => 'meeting_canceled',
                        'title' => '面談キャンセルのお知らせ',
                        'body' => '面談がキャンセルされました。',
                        'url' => '/meetings',
                    ],
                );
            }
        }

    }

    /**
     * 通知データを直接作成する。
     */
    private function createNotification(
        User $user,
        string $type,
        array $data,
    ): void {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => $data,
            'read_at' => fake()->boolean() ? now() : null,
        ]);
    }
}
