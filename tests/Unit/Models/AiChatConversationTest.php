<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatConversationTest extends TestCase
{
    use RefreshDatabase;

    // user_idカラムのUserモデルへのリレーション（BelongsTo）
    public function test_user_relation_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $conversation = AiChatConversation::factory()->create([
            'user_id' => $user->id,
        ]);
        // Act
        $owner = $conversation->user;
        // Assert
        $this->assertTrue($owner->is($user));
    }

    // section_idカラムのSectionモデルへのリレーション（BelongsTo）
    public function test_section_relation_section(): void
    {
        // Arrange
        $section = Section::factory()->create();
        $conversation = AiChatConversation::factory()->create([
            'section_id' => $section->id,
        ]);
        // Act
        $owner = $conversation->section;
        // Assert
        $this->assertTrue($owner->is($section));
    }

    // enrollment_idカラムのEnrollmentモデルへのリレーション（BelongsTo）
    public function test_enrollment_relation_enrollment(): void
    {
        // Arrange
        $enrollment = Enrollment::factory()->create();
        $conversation = AiChatConversation::factory()->create([
            'enrollment_id' => $enrollment->id,
        ]);
        // Act
        $owner = $conversation->enrollment;
        // Assert
        $this->assertTrue($owner->is($enrollment));
    }

    // AiChatMessageモデルへのリレーション（HasMany）
    public function test_messages_relation_ai_chat_message(): void
    {
        // Arrange
        $conversation = AiChatConversation::factory()->create();
        $message = AiChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
        ]);
        // Act
        $messages = $conversation->messages;
        // Assert
        $this->assertTrue($messages->contains($message));
    }

    // last_message_atカラムのキャスト（datetime）
    public function test_last_message_at_cast_datetime(): void
    {
        // Arrange
        $conversation = AiChatConversation::factory()->create(['last_message_at' => '2024-06-01 12:34:56']);
        // Act
        $lastMessageAt = $conversation->last_message_at;
        // Assert
        $this->assertInstanceOf(Carbon::class, $lastMessageAt);
    }
}
