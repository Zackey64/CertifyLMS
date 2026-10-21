<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Models\Meeting;
use App\Models\User;
use App\UseCases\Meeting\IndexAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_meeting_list(): void
    {
        // arrange
        $user = User::factory()->create();
        Meeting::factory()->reserved()->create([
            'student_id' => $user->id,
            'scheduled_at' => now()->addDay(),
        ]);
        // act
        $result = app(IndexAction::class)(
            $user
        );
        // assert
        $this->assertCount(1, $result['meetings']);
    }
}
