<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はスレッド一覧を表示できる
    public function test_student_can_view_thread_index(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        // Act
        $response = $this->actingAs($student)->get(route('qa-board.index'));
        // Assert
        $response->assertOk();
    }

    // コーチはスレッド一覧を表示できる
    public function test_coach_can_view_thread_index(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        // Act
        $response = $this->actingAs($coach)->get(route('qa-board.index'));
        // Assert
        $response->assertOk();
    }

    // 管理者はスレッド一覧を表示できない
    public function test_admin_cannot_view_thread_index(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('qa-board.index'));
        // Assert
        $response->assertForbidden();
    }

    // 管理者は管理者用スレッド一覧画面を表示できる
    public function test_admin_can_view_thread_admin_index(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        // Act
        $response = $this->actingAs($admin)->get(route('admin.qa-board.index'));
        // Assert
        $response->assertOk();
    }

    public function test_qa_thread_status_filter(): void
    {
        // Arrange
        $user = User::factory()->create();
        $thread = QaThread::factory()->create([
            'title' => 'MatchThread',
            'status' => QaThreadStatus::Resolved->value,
        ]);
        QaThread::factory()->create([
            'title' => 'OtherThread',
            'status' => QaThreadStatus::Unresolved->value,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('qa-board.index', ['status' => $thread->status]));
        // Assert
        $response->assertOk();
        $response->assertSee('MatchThread');
        $response->assertDontSee('OtherThread');
    }

    public function test_certification_filter(): void
    {
        // Arrange
        $user = User::factory()->create();
        $matchCertification = Certification::factory()->published()->create([
            'name' => 'Match',
        ]);
        $otherCertification = Certification::factory()->published()->create([
            'name' => 'Other',
        ]);
        QaThread::factory()->create([
            'title' => 'MatchThread',
            'certification_id' => $matchCertification->id,
        ]);
        QaThread::factory()->create([
            'title' => 'OtherThread',
            'certification_id' => $otherCertification->id,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('qa-board.index', ['certification_id' => $matchCertification->id]));
        // Assert
        $response->assertOk();
        $response->assertSee('MatchThread');
        $response->assertDontSee('OtherThread');
    }

    public function test_keyword_filter(): void
    {
        // Arrange
        $user = User::factory()->create();
        QaThread::factory()->create([
            'title' => 'MatchThread',
            'status' => QaThreadStatus::Resolved->value,
        ]);
        QaThread::factory()->create([
            'title' => 'OtherThread',
            'status' => QaThreadStatus::Unresolved->value,
        ]);
        // Act
        $response = $this->actingAs($user)->get(route('qa-board.index', ['keyword' => 'Match']));
        // Assert
        $response->assertOk();
        $response->assertSee('MatchThread');
        $response->assertDontSee('OtherThread');
    }
}
