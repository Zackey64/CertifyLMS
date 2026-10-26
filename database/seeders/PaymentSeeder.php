<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MeetingQuotaTransactionType;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\MeetingPack;
use App\Models\MeetingQuotaTransaction;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::where('role', UserRole::Student->value)->orderBy('created_at')->first();
        $meetingPack = MeetingPack::firstOrFail();

        // 決済待ち
        Payment::factory()->create([
            'user_id' => $student->id,
            'meeting_pack_id' => $meetingPack->id,
            'quantity' => $meetingPack->meeting_count,
            'amount' => $meetingPack->price,
            'status' => PaymentStatus::Pending,
        ]);

        // 決済完了
        $completedPayment = Payment::factory()->create([
            'user_id' => $student->id,
            'meeting_pack_id' => $meetingPack->id,
            'quantity' => $meetingPack->meeting_count,
            'amount' => $meetingPack->price,
            'status' => PaymentStatus::Completed,
            'stripe_payment_intent_id' => 'pi_test_123',
            'paid_at' => now(),
        ]);

        // 購入による面談回数の加算履歴
        MeetingQuotaTransaction::create([
            'user_id' => $completedPayment->user_id,
            'type' => MeetingQuotaTransactionType::Purchased,
            'amount' => $completedPayment->quantity,
            'related_payment_id' => $completedPayment->id,
            'occurred_at' => now(),
        ]);

        // 決済失敗
        Payment::factory()->create([
            'user_id' => $student->id,
            'meeting_pack_id' => $meetingPack->id,
            'quantity' => $meetingPack->meeting_count,
            'amount' => $meetingPack->price,
            'status' => PaymentStatus::Failed,
        ]);

    }
}
