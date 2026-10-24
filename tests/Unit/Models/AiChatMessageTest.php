<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AiChatMessageRole;
use App\Enums\AiChatMessageStatus;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatMessageTest extends TestCase
{
    use RefreshDatabase;

    // conversation_idカラムのAiChatConversationモデルへのリレーション（BelongsTo）
    public function test_conversation_relation_conversation(): void
    {
        // Arrange
        $conversation = AiChatConversation::factory()->create();
        $message = AiChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
        ]);
        // Act
        $owner = $message->conversation;
        // Assert
        $this->assertTrue($owner->is($conversation));
    }

    // roleカラムの列挙型へのキャスト（enum）
    public function test_role_cast_enum(): void
    {
        // Arrange
        $message = AiChatMessage::factory()->create(['role' => AiChatMessageRole::User]);
        // Act
        $role = $message->role;
        // Assert
        $this->assertInstanceOf(AiChatMessageRole::class, $role);
    }

    // statusカラムの列挙型へのキャスト（enum）
    public function test_status_cast_enum(): void
    {
        // Arrange
        $message = AiChatMessage::factory()->create(['status' => AiChatMessageStatus::Pending]);
        // Act
        $status = $message->status;
        // Assert
        $this->assertInstanceOf(AiChatMessageStatus::class, $status);
    }
}
