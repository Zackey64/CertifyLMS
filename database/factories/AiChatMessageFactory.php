<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AiChatMessageRole;
use App\Enums\AiChatMessageStatus;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiChatMessage>
 */
class AiChatMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => AiChatConversation::factory(),
            'content' => fake()->paragraph(),
            'role' => AiChatMessageRole::User,
            'status' => AiChatMessageStatus::Completed,
            'error_detail' => null,
            'response_time_ms' => null,
            'output_tokens' => null,
        ];
    }
}
