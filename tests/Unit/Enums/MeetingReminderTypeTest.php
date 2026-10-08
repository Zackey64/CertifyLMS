<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\MeetingReminderType;
use Tests\TestCase;

class MeetingReminderTypeTest extends TestCase
{
    public function test_enum_lists_four_lifecycle_values(): void
    {
        $values = array_map(fn (MeetingReminderType $s) => $s->value, MeetingReminderType::cases());

        $this->assertEqualsCanonicalizing(
            ['eve', 'one_hour_before'],
            $values,
        );
    }

    public function test_japanese_labels(): void
    {
        $this->assertSame('前日', MeetingReminderType::Eve->label());
        $this->assertSame('1時間前', MeetingReminderType::OneHourBefore->label());
    }
}
