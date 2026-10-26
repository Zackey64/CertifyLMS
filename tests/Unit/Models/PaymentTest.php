<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\PaymentStatus;
use App\Models\MeetingPack;
use App\Models\MeetingQuotaTransaction;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    // user_idカラムのUserモデルへのリレーション（BelongsTo）
    public function test_user_relation_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
        ]);
        // Act
        $owner = $payment->user;
        // Assert
        $this->assertTrue($owner->is($user));
    }

    // meeting_pack_idカラムのMeetingPackモデルへのリレーション（BelongsTo）
    public function test_meeting_pack_relation_meeting_pack(): void
    {
        // Arrange
        $section = MeetingPack::factory()->create();
        $payment = Payment::factory()->create([
            'meeting_pack_id' => $section->id,
        ]);
        // Act
        $owner = $payment->meetingPack;
        // Assert
        $this->assertTrue($owner->is($section));
    }

    // MeetingQuotaTransactionモデルへのリレーション（HasMany）
    public function test_relation_meeting_quota_transaction(): void
    {
        // Arrange
        $payment = Payment::factory()->create();
        $quotaTransaction = MeetingQuotaTransaction::factory()->create([
            'related_payment_id' => $payment->id,
        ]);
        // Act
        $owner = $payment->quotaTransactions;
        // Assert
        $this->assertTrue($owner->contains($quotaTransaction));
    }

    // quantityカラムの整数型へのキャスト（integer）
    public function test_quantity_cast_integer(): void
    {
        // Arrange
        $payment = Payment::factory()->create(['quantity' => 1]);
        // Act
        $status = $payment->quantity;
        // Assert
        $this->assertIsInt($status);
    }

    // amountカラムの整数型へのキャスト（integer）
    public function test_amount_cast_integer(): void
    {
        // Arrange
        $payment = Payment::factory()->create(['amount' => 1]);
        // Act
        $status = $payment->amount;
        // Assert
        $this->assertIsInt($status);
    }

    // statusカラムの列挙型へのキャスト（enum）
    public function test_status_cast_enum(): void
    {
        // Arrange
        $payment = Payment::factory()->create(['status' => PaymentStatus::Pending]);
        // Act
        $status = $payment->status;
        // Assert
        $this->assertInstanceOf(PaymentStatus::class, $status);
    }

    // paid_atカラムのキャスト（datetime）
    public function test_paid_at_cast_datetime(): void
    {
        // Arrange
        $payment = Payment::factory()->create(['paid_at' => '2024-06-01 12:34:56']);
        // Act
        $paidAt = $payment->paid_at;
        // Assert
        $this->assertInstanceOf(Carbon::class, $paidAt);
    }
}
