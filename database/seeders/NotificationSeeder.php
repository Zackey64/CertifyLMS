<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
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

        foreach ([$student, $coach] as $user) {
            if ($user === null) {
                continue;
            }

            for ($i = 0; $i < 5; $i++) {
                //
                $notification = new ChatMessageReceivedNotification;
                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => $notification::class,
                    'data' => $notification->toDatabase($user),
                    'read_at' => fake()->boolean() ? now() : null,
                ]);
                //
                $notification = new QaReplyReceivedNotification;
                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => $notification::class,
                    'data' => $notification->toDatabase($user),
                    'read_at' => fake()->boolean() ? now() : null,
                ]);
                //
                $notification = new MeetingReservedNotification;
                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => $notification::class,
                    'data' => $notification->toDatabase($user),
                    'read_at' => fake()->boolean() ? now() : null,
                ]);
                //
                $notification = new MeetingCanceledNotification;
                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => $notification::class,
                    'data' => $notification->toDatabase($user),
                    'read_at' => fake()->boolean() ? now() : null,
                ]);
            }
        }

    }
}
