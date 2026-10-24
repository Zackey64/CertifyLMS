<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AiChatMessageRole;
use PHPUnit\Framework\TestCase;

class AiChatMessageRoleTest extends TestCase
{
    public function test_enum_lists_four_lifecycle_values(): void
    {
        $values = array_map(fn (AiChatMessageRole $s) => $s->value, AiChatMessageRole::cases());

        $this->assertEqualsCanonicalizing(
            ['user', 'assistant'],
            $values,
        );
    }

    public function test_japanese_labels(): void
    {
        $this->assertSame('ユーザー', AiChatMessageRole::User->label());
        $this->assertSame('アシスタント', AiChatMessageRole::Assistant->label());
    }
}
