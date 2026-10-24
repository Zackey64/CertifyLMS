<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AiChatMessageStatus;
use PHPUnit\Framework\TestCase;

class AiChatMessageStatusTest extends TestCase
{
    public function test_enum_lists_four_lifecycle_values(): void
    {
        $values = array_map(fn (AiChatMessageStatus $s) => $s->value, AiChatMessageStatus::cases());

        $this->assertEqualsCanonicalizing(
            ['pending', 'completed', 'error'],
            $values,
        );
    }

    public function test_japanese_labels(): void
    {
        $this->assertSame('保留中', AiChatMessageStatus::Pending->label());
        $this->assertSame('完了', AiChatMessageStatus::Completed->label());
        $this->assertSame('エラー', AiChatMessageStatus::Error->label());
    }
}
