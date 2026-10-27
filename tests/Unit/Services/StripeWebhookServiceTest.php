<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\MeetingQuotaTransactionType;
use App\Enums\PaymentStatus;
use App\Models\MeetingQuotaTransaction;
use App\Models\Payment;
use App\Models\User;
use App\Services\StripeWebhookService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    private StripeWebhookService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new StripeWebhookService;
    }

    /**
     * 完了した支払いがミーティングクオータトランザクションを作成することを確認するテスト
     */
    public function test_completed_payment_creates_quota_transaction(): void
    {
        // Arrange
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'quantity' => 5,
            'status' => PaymentStatus::Pending,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        $session = $this->makeSession(
            (string) $payment->id,
            'cs_test_123',
        );

        // Act
        $this->service->handleCheckoutSessionCompleted($session);

        // Assert
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Succeeded->value,
            'stripe_payment_intent_id' => 'pi_test_123',
        ]);

        $this->assertDatabaseHas('meeting_quota_transactions', [
            'user_id' => $user->id,
            'type' => MeetingQuotaTransactionType::Purchased->value,
            'amount' => 5,
            'related_payment_id' => $payment->id,
        ]);
    }

    /**
     * 未払いのセッションでは支払いが更新されないことを確認するテスト
     */
    public function test_unpaid_session_does_not_update_payment(): void
    {
        // Arrange
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'status' => PaymentStatus::Pending,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        $session = $this->makeSession(
            (string) $payment->id,
            'cs_test_123',
            'unpaid',
        );

        // Act
        $this->service->handleCheckoutSessionCompleted($session);

        // Assert
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Pending->value,
        ]);

        $this->assertDatabaseCount('meeting_quota_transactions', 0);
    }

    /**
     * 異なるチェックアウトセッションでは支払いが更新されないことを確認するテスト
     */
    public function test_different_checkout_session_does_not_update_payment(): void
    {
        // Arrange
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'status' => PaymentStatus::Pending,
            'stripe_checkout_session_id' => 'cs_original',
        ]);

        $session = $this->makeSession(
            (string) $payment->id,
            'cs_different',
        );

        // Act
        $this->service->handleCheckoutSessionCompleted($session);

        // Assert
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Pending->value,
        ]);

        $this->assertDatabaseCount('meeting_quota_transactions', 0);
    }

    /**
     * 重複した通知では重複したトランザクションが作成されないことを確認するテスト
     */
    public function test_duplicate_notification_does_not_create_duplicate_transaction(): void
    {
        // Arrange
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'quantity' => 5,
            'status' => PaymentStatus::Pending,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        $session = $this->makeSession(
            (string) $payment->id,
            'cs_test_123',
        );

        // Act
        $this->service->handleCheckoutSessionCompleted($session);
        $this->service->handleCheckoutSessionCompleted($session);

        // Assert
        $this->assertSame(
            1,
            MeetingQuotaTransaction::where(
                'related_payment_id',
                $payment->id,
            )->count(),
        );
    }

    /**
     * 支払いが存在しない場合に例外がスローされることを確認するテスト
     */
    public function test_throws_exception_when_payment_does_not_exist(): void
    {
        // Arrange
        $session = $this->makeSession('999999', 'cs_test_123');

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->service->handleCheckoutSessionCompleted($session);
    }

    private function makeSession(
        string $paymentId,
        string $sessionId,
        string $paymentStatus = 'paid',
    ): object {
        return json_decode(json_encode([
            'id' => $sessionId,
            'payment_status' => $paymentStatus,
            'payment_intent' => 'pi_test_123',
            'metadata' => [
                'payment_id' => $paymentId,
            ],
        ], JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
    }
}
