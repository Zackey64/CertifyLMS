<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\AiChatConversation;

use App\Http\Requests\AiChatConversation\StoreRequest;
use App\Models\Section;
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
            'section_id' => Section::factory()->create()->id,
            'title' => '会話タイトル',
            'message' => '内容',
            'source' => 'widget',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時にセクションIDが空でもいい
    public function test_validation_passes_section_id_nullable(): void
    {
        // Arrange
        $data = [
            'section_id' => null,
            'title' => '会話タイトル',
            'message' => '内容',
            'source' => 'widget',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時にタイトルが空でもいい
    public function test_validation_passes_title_nullable(): void
    {
        // Arrange
        $data = [
            'section_id' => Section::factory()->create()->id,
            'title' => null,
            'message' => '内容',
            'source' => 'widget',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時に内容が空でもいい
    public function test_validation_passes_message_nullable(): void
    {
        // Arrange
        $data = [
            'section_id' => Section::factory()->create()->id,
            'title' => '会話タイトル',
            'message' => null,
            'source' => 'widget',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時にsourceが空だとエラー
    public function test_validation_fails_source_required(): void
    {
        // Arrange
        $data = [
            'source' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('source', $validator->errors()->toArray());
    }
}
