<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MeetingReminderStatus;
use App\Enums\MeetingReminderType;
use App\Models\Meeting;
use App\Models\MeetingReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingReminder>
 */
class MeetingReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'recipient_id' => User::factory(),
            'type' => MeetingReminderType::Eve,
            'status' => MeetingReminderStatus::Sent,
            'sent_at' => now(),
        ];
    }
}
