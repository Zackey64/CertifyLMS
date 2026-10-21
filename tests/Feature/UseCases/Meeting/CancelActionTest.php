<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use App\UseCases\Meeting\CancelAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_cancel_meeting(): void
    {
        // arrange
        $user = User::factory()->create();
        $meeting = Meeting::factory()->reserved()->create([
            'student_id' => $user->id,
            'scheduled_at' => now()->addDay(),
        ]);
        // act
        $result = app(CancelAction::class)(
            $meeting,
            $user,
        );
        // assert
        $this->assertSame(
            MeetingStatus::Canceled,
            $meeting->fresh()->status,
        );
    }
}
