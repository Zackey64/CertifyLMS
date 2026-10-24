<?php

declare(strict_types=1);

namespace Tests\Feature\Http\AiChatConversation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はAIチャット会話を作成できる
    public function test_student_can_store_conversation(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $data = [
            'title' => 'テストタイトル',
            'content' => 'テスト内容',
            'source' => 'widget',
        ];
        // Act
        $response = $this->actingAs($student)->post(route('ai-chat.conversations.store'), $data);
        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'conversation' => [
                    'id',
                    'title',
                ],
            ]);
        $this->assertDatabaseHas('ai_chat_conversations', [
            'user_id' => $student->id,
        ]);
    }

    // コーチはAIチャット会話を作成できない
    public function test_coach_cannot_store_conversation(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $data = [
            'content' => 'テスト内容',
            'source' => 'widget',
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('ai-chat.conversations.store'), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者はAIチャット会話を作成できない
    public function test_admin_cannot_store_conversation(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $data = [
            'content' => 'テスト内容',
            'source' => 'widget',
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('ai-chat.conversations.store'), $data);
        // Assert
        $response->assertForbidden();
    }
}
