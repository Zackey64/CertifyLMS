<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\QaThread;

use App\Http\Requests\QaThread\UpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_title_required(): void
    {
        // Arrange
        $data = [
            'title' => '',
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_validation_fails_title_max(): void
    {
        // Arrange
        $data = [
            'title' => str_repeat('a', 201),
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_validation_fails_body_required(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => '',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }

    public function test_validation_fails_body_max(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => str_repeat('a', 5001),
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }
}
