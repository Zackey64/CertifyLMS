<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Avatar;

use App\Http\Requests\Avatar\StoreRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreRequestTest extends TestCase
{
    use RefreshDatabase;

    // 更新時に正常にバリデーションを通過
    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // アバターが空だとエラー
    public function test_validation_fails_avatar_required(): void
    {
        // Arrange
        $data = [
            'avatar' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('avatar', $validator->errors()->toArray());
    }

    // アバターが画像形式でないとエラー
    public function test_validation_fails_avatar_image(): void
    {
        // Arrange
        $data = [
            'avatar' => UploadedFile::fake()->create('avatar.txt', 100),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('avatar', $validator->errors()->toArray());
    }

    // アバターが2MBを超えるとエラー
    public function test_validation_fails_avatar_max(): void
    {
        // Arrange
        $data = [
            'avatar' => UploadedFile::fake()->create('avatar.jpg', 2049),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('avatar', $validator->errors()->toArray());
    }
}
