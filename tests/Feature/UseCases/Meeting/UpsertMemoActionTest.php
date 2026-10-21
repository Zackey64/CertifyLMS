<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Models\Meeting;
use App\Models\MeetingMemo;
use App\UseCases\Meeting\UpsertMemoAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpsertMemoActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_meeting_memo(): void
    {
        // arrange
        $meeting = Meeting::factory()->reserved()->create();
        // act
        app(UpsertMemoAction::class)(
            $meeting,
            '面談メモ',
        );
        // assert
        $this->assertDatabaseHas('meeting_memos', [
            'meeting_id' => $meeting->id,
            'body' => '面談メモ',
        ]);
    }

    public function test_can_update_meeting_memo(): void
    {
        // arrange
        $meeting = Meeting::factory()->reserved()->create();
        MeetingMemo::factory()->create([
            'meeting_id' => $meeting->id,
            'body' => '変更前のメモ',
        ]);
        // act
        app(UpsertMemoAction::class)(
            $meeting,
            '変更後のメモ',
        );
        // assert
        $this->assertDatabaseHas('meeting_memos', [
            'meeting_id' => $meeting->id,
            'body' => '変更後のメモ',
        ]);
        $this->assertDatabaseMissing('meeting_memos', [
            'meeting_id' => $meeting->id,
            'body' => '変更前のメモ',
        ]);
    }
}
