<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\AiChatConversation;
use App\Models\User;
use App\Policies\AiChatConversationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatConversationPolicyTest extends TestCase
{
    use RefreshDatabase;

    // 本人は会話を閲覧できる
    public function test_owner_can_view_conversation(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->view($owner, $conversation);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は会話を閲覧できない
    public function test_non_owner_cannot_view_conversation(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->view($nonOwner, $conversation);
        // Assert
        $this->assertFalse($result);
    }

    // 本人は会話を更新できる
    public function test_owner_can_update_conversation(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->update($owner, $conversation);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は会話を更新できない
    public function test_non_owner_cannot_update_conversation(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->update($nonOwner, $conversation);
        // Assert
        $this->assertFalse($result);
    }

    // 本人は会話を削除できる
    public function test_owner_can_delete_conversation(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->delete($owner, $conversation);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外は会話を削除できない
    public function test_non_owner_cannot_delete_conversation(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->delete($nonOwner, $conversation);
        // Assert
        $this->assertFalse($result);
    }

    // 本人の受講登録に対してメッセージを作成できる
    public function test_owner_can_create_message(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->store($owner, $conversation);
        // Assert
        $this->assertTrue($result);
    }

    // 本人以外はメッセージを作成できない
    public function test_non_owner_cannot_create_message(): void
    {
        // Arrange
        $owner = User::factory()->student()->create();
        $nonOwner = User::factory()->student()->create();
        $conversation = AiChatConversation::factory()->for($owner)->create();
        $policy = new AiChatConversationPolicy;
        // Act
        $result = $policy->store($nonOwner, $conversation);
        // Assert
        $this->assertFalse($result);
    }
}
