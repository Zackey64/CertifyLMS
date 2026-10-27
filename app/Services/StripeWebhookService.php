<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MeetingQuotaTransactionType;
use App\Enums\PaymentStatus;
use App\Models\MeetingQuotaTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class StripeWebhookService
{
    public function handleCheckoutSessionCompleted(object $session): void
    {
        // 決済が完了していることを確認
        if (($session->payment_status ?? null) !== 'paid') {
            return;
        }

        $paymentId = $session->metadata->payment_id ?? null;

        if (! $paymentId) {
            throw new \InvalidArgumentException('Payment ID is missing.');
        }

        DB::transaction(function () use ($session, $paymentId): void {
            $payment = Payment::whereKey($paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            // 別のCheckoutセッションによる処理を防止
            if ($payment->stripe_checkout_session_id !== $session->id) {
                return;
            }

            // 二重付与を防止
            if ($payment->status === PaymentStatus::Succeeded) {
                return;
            }

            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'stripe_payment_intent_id' => $session->payment_intent,
                'paid_at' => now(),
            ]);

            MeetingQuotaTransaction::create([
                'user_id' => $payment->user_id,
                'type' => MeetingQuotaTransactionType::Purchased,
                'amount' => $payment->quantity,
                'related_payment_id' => $payment->id,
                'occurred_at' => now(),
            ]);
        });
    }
}
