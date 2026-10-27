<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(
        Request $request,
        StripeWebhookService $stripeWebhookService,
    ): Response {

        Log::info('Stripe webhook called');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                config('services.stripe.webhook_secret'),
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid webhook', 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response('Ignored', 200);
        }

        $session = $event->data->object;

        if (($session->payment_status ?? null) !== 'paid') {
            return response('Payment not completed', 200);
        }

        if (! ($session->metadata->payment_id ?? null)) {
            return response('Payment ID missing', 400);
        }

        $stripeWebhookService->handleCheckoutSessionCompleted($session);

        return response('OK', 200);
    }
}
