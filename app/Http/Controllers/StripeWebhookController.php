<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\MeetingQuotaTransactionType;
use App\Enums\PaymentStatus;
use App\Models\MeetingQuotaTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        Log::info('Stripe webhook called');

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature ?? '',
                $secret,
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            return response('Invalid webhook', 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response('Ignored', 200);
        }

        $session = $event->data->object;

        // 決済が完了していることを確認
        if ($session->payment_status !== 'paid') {
            return response('Payment not completed', 200);
        }

        $paymentId = $session->metadata->payment_id ?? null;

        if (! $paymentId) {
            return response('Payment ID missing', 400);
        }

        DB::transaction(function () use ($session, $paymentId) {
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

        return response('OK', 200);
    }
}
