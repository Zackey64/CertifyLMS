<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\MeetingPackStatus;
use PHPUnit\Framework\TestCase;

class MeetingPackStatusTest extends TestCase
{
    public function test_enum_lists_four_lifecycle_values(): void
    {
        $values = array_map(fn (MeetingPackStatus $s) => $s->value, MeetingPackStatus::cases());

        $this->assertEqualsCanonicalizing(
            ['draft', 'published', 'archived'],
            $values,
        );
    }

    public function test_japanese_labels(): void
    {
        $this->assertSame('下書き', MeetingPackStatus::Draft->label());
        $this->assertSame('公開中', MeetingPackStatus::Published->label());
        $this->assertSame('アーカイブ', MeetingPackStatus::Archived->label());
    }
}
