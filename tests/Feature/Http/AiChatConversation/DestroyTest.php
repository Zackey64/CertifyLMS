<?php

declare(strict_types=1);

namespace Tests\Feature\Http\AiChatConversation;

use App\Models\AiChatConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はAIチャット会話を削除できる
    public function test_student_can_destroy_conversation(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->create(['user_id' => $student->id]);
        // Act
        $response = $this->actingAs($student)->delete(route('ai-chat.conversations.destroy', $conversation));
        // Assert
        $response->assertRedirect(route('ai-chat.index'));
    }

    // コーチはAIチャット会話を削除できない
    public function test_coach_cannot_destroy_conversation(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $conversation = AiChatConversation::factory()->create(['user_id' => $coach->id]);
        // Act
        $response = $this->actingAs($coach)->delete(route('ai-chat.conversations.destroy', $conversation));
        // Assert
        $response->assertForbidden();
    }

    // 管理者はAIチャット会話を削除できない
    public function test_admin_cannot_destroy_conversation(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $conversation = AiChatConversation::factory()->create(['user_id' => $admin->id]);
        // Act
        $response = $this->actingAs($admin)->delete(route('ai-chat.conversations.destroy', $conversation));
        // Assert
        $response->assertForbidden();
    }
}
