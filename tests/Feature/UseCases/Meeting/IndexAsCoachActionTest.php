<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Models\Meeting;
use App\Models\User;
use App\UseCases\Meeting\IndexAsCoachAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexAsCoachActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_coach_meeting_list(): void
    {
        // arrange
        $user = User::factory()->create();
        Meeting::factory()->reserved()->create([
            'coach_id' => $user->id,
            'scheduled_at' => now()->addDay(),
        ]);
        // act
        $result = app(IndexAsCoachAction::class)(
            $user,
            'upcoming',
            null,
            null,
        );
        // assert
        $this->assertCount(1, $result);
    }
}
