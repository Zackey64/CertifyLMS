<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QaThread>
 */
class QaThreadFactory extends Factory
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
            'certification_id' => Certification::factory(),
            'title' => fake()->realTextBetween(10, 50),
            'body' => fake()->realTextBetween(50, 100),
            'status' => QaThreadStatus::Unresolved,
            'resolved_at' => null,
        ];
    }
}
