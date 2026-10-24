<?php

declare(strict_types=1);

namespace Tests\Feature\Http\AiChatMessage;

use App\Models\AiChatConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 受講生はAIチャットメッセージを作成できる
    public function test_student_can_store_message(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'テスト回答'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Arrange
        $student = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->create(['user_id' => $student->id]);
        $data = [
            'content' => 'テスト内容',
        ];
        // Act
        $response = $this->actingAs($student)->post(route('ai-chat.conversations.messages.store', $conversation), $data);
        // Assert
        $response->assertOk();
    }

    // コーチはAIチャットメッセージを作成できない
    public function test_coach_cannot_store_message(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        $conversation = AiChatConversation::factory()->create(['user_id' => $coach->id]);
        $data = [
            'content' => 'テスト内容',
        ];
        // Act
        $response = $this->actingAs($coach)->post(route('ai-chat.conversations.messages.store', $conversation), $data);
        // Assert
        $response->assertForbidden();
    }

    // 管理者はAIチャットメッセージを作成できない
    public function test_admin_cannot_store_message(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $conversation = AiChatConversation::factory()->create(['user_id' => $admin->id]);
        $data = [
            'content' => 'テスト内容',
        ];
        // Act
        $response = $this->actingAs($admin)->post(route('ai-chat.conversations.messages.store', $conversation), $data);
        // Assert
        $response->assertForbidden();
    }
}
