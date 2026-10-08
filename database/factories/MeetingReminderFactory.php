<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MeetingReminderType;
use App\Models\Meeting;
use App\Models\MeetingReminder;
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
            'type' => MeetingReminderType::Eve,
            'sent_at' => now(),
        ];
    }
}
