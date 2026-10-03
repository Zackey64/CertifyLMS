<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AnnouncementTargetType;
use PHPUnit\Framework\TestCase;

class AnnouncementTargetTypeTest extends TestCase
{
    public function test_enum_lists_four_lifecycle_values(): void
    {
        $values = array_map(fn (AnnouncementTargetType $s) => $s->value, AnnouncementTargetType::cases());

        $this->assertEqualsCanonicalizing(
            ['all_students', 'certification', 'user'],
            $values,
        );
    }

    public function test_japanese_labels(): void
    {
        $this->assertSame('全受講生', AnnouncementTargetType::AllStudents->label());
        $this->assertSame('対象資格指定', AnnouncementTargetType::Certification->label());
        $this->assertSame('対象受講生指定', AnnouncementTargetType::User->label());
    }
}
