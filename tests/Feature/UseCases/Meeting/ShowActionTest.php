<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Models\Meeting;
use App\Models\User;
use App\UseCases\Meeting\ShowAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_meeting(): void
    {
        // arrange
        $user = User::factory()->create();
        $meeting = Meeting::factory()->reserved()->create([
            'student_id' => $user->id,
            'scheduled_at' => now()->addDay(),
        ]);
        // act
        $result = app(ShowAction::class)(
            $meeting
        );
        // assert
        $this->assertEquals($meeting->id, $result->id);
    }
}
