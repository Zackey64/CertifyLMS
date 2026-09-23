<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\QaThreadStatus;
use Tests\TestCase;

class QaThreadStatusTest extends TestCase
{
    public function test_enum_lists_four_lifecycle_values(): void
    {
        $values = array_map(fn (QaThreadStatus $s) => $s->value, QaThreadStatus::cases());

        $this->assertEqualsCanonicalizing(
            ['unresolved', 'resolved'],
            $values,
        );
    }

    public function test_japanese_labels(): void
    {
        $this->assertSame('未解決', QaThreadStatus::Unresolved->label());
        $this->assertSame('解決済', QaThreadStatus::Resolved->label());
    }
}
