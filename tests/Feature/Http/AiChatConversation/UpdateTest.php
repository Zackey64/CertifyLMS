<?php

declare(strict_types=1);

namespace Tests\Feature\Http\AiChatConversation;

use App\Models\AiChatConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はAIチャット会話を更新できる
    public function test_student_can_update_conversation(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->create(['user_id' => $student->id]);
        $data = [
            'title' => 'テストタイトル',
        ];
        // Act
        $response = $this->actingAs($student)->patch(route('ai-chat.conversations.update', $conversation), $data);
        // Assert
        $conversation->refresh();
        $response->assertRedirect(route('ai-chat.conversations.show', $conversation));
    }

    // コーチはAIチャット会話を更新できない
    public function test_coach_cannot_update_conversation(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $data = [
            'title' => 'テストタイトル',
        ];
        // Act
        $response = $this->actingAs($coach)->patch(route('ai-chat.conversations.update', AiChatConversation::factory()->create(['user_id' => $coach->id])), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者はAIチャット会話を更新できない
    public function test_admin_cannot_update_conversation(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $data = [
            'title' => 'テストタイトル',
        ];
        // Act
        $response = $this->actingAs($admin)->patch(route('ai-chat.conversations.update', AiChatConversation::factory()->create(['user_id' => $admin->id])), $data);
        // Assert
        $response->assertForbidden();
    }
}
