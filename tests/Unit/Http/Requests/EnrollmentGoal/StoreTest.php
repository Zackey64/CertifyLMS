<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\EnrollmentGoal;

use App\Http\Requests\EnrollmentGoal\StoreRequest;
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
            'title' => 'テスト目標名',
            'description' => '詳細説明',
            'target_date' => now()->addDays(7),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時にタイトルが空だとエラー
    public function test_validation_fails_title_required(): void
    {
        // Arrange
        $data = [
            'title' => null,
            'description' => '詳細説明',
            'target_date' => now()->addDays(7),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    // 作成時にタイトルが100文字を超えるとエラー
    public function test_validation_fails_title_max(): void
    {
        // Arrange
        $data = [
            'title' => str_repeat('a', 101),
            'description' => '詳細説明',
            'target_date' => now()->addDays(7),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    // 作成時に詳細が空でもバリデーションを通過
    public function test_validation_passes_description_nullable(): void
    {
        // Arrange
        $data = [
            'title' => 'テスト目標名',
            'description' => null,
            'target_date' => now()->addDays(7),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時に詳細が1000文字を超えるとエラー
    public function test_validation_fails_description_max(): void
    {
        // Arrange
        $data = [
            'title' => 'テスト目標名',
            'description' => str_repeat('a', 1001),
            'target_date' => now()->addDays(7),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    // 作成時に目標日が空でもバリデーションを通過
    public function test_validation_passes_target_date_nullable(): void
    {
        // Arrange
        $data = [
            'title' => 'テスト目標名',
            'description' => '詳細説明',
            'target_date' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時に目標日が過去日だとエラー
    public function test_validation_fails_target_date_past(): void
    {
        // Arrange
        $data = [
            'title' => 'テスト目標名',
            'description' => '詳細説明',
            'target_date' => now()->subDays(1),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('target_date', $validator->errors()->toArray());
    }
}
