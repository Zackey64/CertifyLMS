<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\PaymentStatus;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_enum_lists_three_lifecycle_values(): void
    {
        $values = array_map(fn (PaymentStatus $s) => $s->value, PaymentStatus::cases());

        $this->assertEqualsCanonicalizing(
            [ 'succeeded', 'failed','pending', 'refunded'],
            $values,
        );
    }

    public function test_japanese_labels(): void
    {
        $this->assertSame('決済待ち', PaymentStatus::Pending->label());
        $this->assertSame('決済完了', PaymentStatus::Succeeded->label());
        $this->assertSame('決済失敗', PaymentStatus::Failed->label());
        $this->assertSame('返金済み', PaymentStatus::Refunded->label());
    }
}
