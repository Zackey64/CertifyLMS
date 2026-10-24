<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\AiChatMessage;

use App\Http\Requests\AiChatMessage\StoreRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 作成時に正常にバリデーションを通過
    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'content' => '内容',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時に内容が空だとエラー
    public function test_validation_fails_content_required(): void
    {
        // Arrange
        $data = [
            'content' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    // 作成時に内容が2000文字を超えるとエラー
    public function test_validation_fails_content_max(): void
    {
        // Arrange
        $data = [
            'content' => str_repeat('a', 2001),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }
}
