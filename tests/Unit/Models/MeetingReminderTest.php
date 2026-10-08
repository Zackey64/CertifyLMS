<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\MeetingReminderType;
use App\Models\Meeting;
use App\Models\MeetingReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingReminderTest extends TestCase
{
    use RefreshDatabase;

    // meeting_idカラムのMeetingモデルへのリレーション（BelongsTo）
    public function test_emeeting_id_relation_meeting(): void
    {
        // Arrange
        $meeting = Meeting::factory()->create();
        $reminder = MeetingReminder::factory()->for($meeting)->create();
        // Act
        $owner = $reminder->meeting;
        // Assert
        $this->assertTrue($owner->is($meeting));
    }

    // typeカラムの列挙型へのキャスト（enum）
    public function test_type_cast_enum(): void
    {
        // Arrange
        $reminder = MeetingReminder::factory()->create(['type' => MeetingReminderType::Eve]);
        // Act
        $fresh = $reminder->fresh();
        // Assert
        $this->assertInstanceOf(MeetingReminderType::class, $fresh->type);
    }

    // target_dateカラムの列挙型へのキャスト（date）
    public function test_sent_at_cast_datetime(): void
    {
        // Arrange
        $reminder = MeetingReminder::factory()->create(['sent_at' => '2024-06-01']);
        // Act
        $fresh = $reminder->fresh();
        // Assert
        $this->assertInstanceOf(Carbon::class, $fresh->sent_at);
    }
}
