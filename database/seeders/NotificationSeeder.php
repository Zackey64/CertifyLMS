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

            for ($i = 0; $i < 5; $i++) {
                //
                $user->notify(new ChatMessageReceivedNotification($room));
                $notification = $user->notifications()->latest()->first();
                if (fake()->boolean()) {
                    $notification->markAsRead();
                }
                //
                $user->notify(new QaReplyReceivedNotification($thread));
                $notification = $user->notifications()->latest()->first();
                if (fake()->boolean()) {
                    $notification->markAsRead();
                }
                //
                $user->notify(new MeetingReservedNotification);
                $notification = $user->notifications()->latest()->first();
                if (fake()->boolean()) {
                    $notification->markAsRead();
                }
                //
                $user->notify(new MeetingCanceledNotification);
                $notification = $user->notifications()->latest()->first();
                if (fake()->boolean()) {
                    $notification->markAsRead();
                }
            }
        }

    }
}
