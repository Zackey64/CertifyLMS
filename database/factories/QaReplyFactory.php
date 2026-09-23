<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QaReply>
 */
class QaReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'qa_thread_id' => QaThread::factory(),
            'body' => fake()->realTextBetween(50, 100),
        ];
    }
}
