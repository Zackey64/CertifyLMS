<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\EnrollmentNote;

use App\Http\Requests\EnrollmentNote\StoreRequest;
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
            'body' => 'テストメモ',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時にタイトルが空だとエラー
    public function test_validation_fails_body_required(): void
    {
        // Arrange
        $data = [
            'body' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }

    // 作成時にメモが2000文字を超えるとエラー
    public function test_validation_fails_body_max(): void
    {
        // Arrange
        $data = [
            'body' => str_repeat('a', 2001),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }
}
