<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\AiChatConversation;

use App\Http\Requests\AiChatConversation\UpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    // 更新時に正常にバリデーションを通過
    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'title' => '会話タイトル',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 更新時にタイトルが空だとエラー
    public function test_validation_fails_title_required(): void
    {
        // Arrange
        $data = [
            'title' => null,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    // 更新時にタイトルが100文字を超えるとエラー
    public function test_validation_fails_title_max(): void
    {
        // Arrange
        $data = [
            'title' => str_repeat('a', 101),
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }
}
