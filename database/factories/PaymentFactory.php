<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\MeetingPack;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'meeting_pack_id' => MeetingPack::factory(),
            'quantity' => 1,
            'amount' => 3000,
            'status' => PaymentStatus::Pending,
            'stripe_checkout_session_id' => null,
            'stripe_payment_intent_id' => null,
            'paid_at' => null,
        ];
    }
}
