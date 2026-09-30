<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Profile;

use App\Http\Requests\Profile\UpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    // 更新時に正常にバリデーションを通過
    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト氏名',
            'bio' => '自己紹介',
            'meeting_url' => 'https://test',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 更新時に名前が空だとエラー
    public function test_validation_fails_name_required(): void
    {
        // Arrange
        $data = [
            'name' => null,
            'bio' => '自己紹介',
            'meeting_url' => 'https://test',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    // 更新時に名前が100文字を超えるとエラー
    public function test_validation_fails_name_max(): void
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 51),
            'bio' => '自己紹介',
            'meeting_url' => 'https://test',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    // 更新時に自己紹介が空でもバリデーションを通過
    public function test_validation_passes_bio_nullable(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト氏名',
            'bio' => null,
            'meeting_url' => 'https://test',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 更新時に自己紹介が1000文字を超えるとエラー
    public function test_validation_fails_bio_max(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト氏名',
            'bio' => str_repeat('a', 1001),
            'meeting_url' => 'https://test',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('bio', $validator->errors()->toArray());
    }

    // 更新時にミーティングURLが空でもバリデーションを通過
    public function test_validation_passes_meeting_url_nullable(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト氏名',
            'bio' => '自己紹介',
            'meeting_url' => null,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 更新時にミーティングURLがURL形式でないとエラー
    public function test_validation_fails_meeting_url_url(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト氏名',
            'bio' => '自己紹介',
            'meeting_url' => 'invalid-url',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('meeting_url', $validator->errors()->toArray());
    }
}
