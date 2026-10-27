<?php

declare(strict_types=1);

namespace Tests\Feature\Http\StripeWebhook;

use App\Enums\MeetingQuotaTransactionType;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Services\StripeWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HandleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 完了した支払いでトランザクションが作成されることを確認するテスト
     */
    public function test_completed_payment_creates_quota_transaction(): void
    {
        // Arrange
        config(['services.stripe.webhook_secret' => 'whsec_test']);
        $user = User::factory()->create();
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'quantity' => 5,
            'status' => PaymentStatus::Pending,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);
        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_status' => 'paid',
                    'payment_intent' => 'pi_test_123',
                    'metadata' => ['payment_id' => (string) $payment->id],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = time();
        $signature = hash_hmac(
            'sha256',
            $timestamp.'.'.$payload,
            'whsec_test',
        );

        // Act
        $response = $this->call(
            'POST',
            '/webhooks/stripe',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            ],
            $payload,
        );

        // Assert
        $response->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Succeeded->value,
        ]);

        $this->assertDatabaseHas('meeting_quota_transactions', [
            'user_id' => $user->id,
            'type' => MeetingQuotaTransactionType::Purchased->value,
            'amount' => 5,
            'related_payment_id' => $payment->id,
        ]);
    }

    /**
     * 完了したイベントがサービスを呼び出すことを確認するテスト
     */
    public function test_completed_event_calls_service(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        $service = Mockery::mock(StripeWebhookService::class);

        $service->shouldReceive('handleCheckoutSessionCompleted')
            ->once()
            ->with(Mockery::on(
                fn (object $session): bool => $session->id === 'cs_test_123'
                    && $session->payment_status === 'paid'
            ));

        $this->app->instance(StripeWebhookService::class, $service);

        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_status' => 'paid',
                    'payment_intent' => 'pi_test_123',
                    'metadata' => [
                        'payment_id' => '1',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = time();

        $signature = hash_hmac(
            'sha256',
            $timestamp.'.'.$payload,
            'whsec_test',
        );

        $response = $this->call(
            'POST',
            '/webhooks/stripe',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            ],
            $payload,
        );

        $response->assertOk();
    }
}
