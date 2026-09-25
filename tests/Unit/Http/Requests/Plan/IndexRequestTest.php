<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Plan;

use App\Enums\PlanStatus;
use App\Http\Requests\Plan\IndexRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'keyword' => 'テストキーワード',
            'status' => PlanStatus::Published->value,
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_status_nullable(): void
    {
        // Arrange
        $data = [
            'status' => '',
            'keyword' => 'テスト',
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_status_enum(): void
    {
        // Arrange
        $data = [
            'status' => 'invalid_status',
            'keyword' => 'テスト',
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_passes_keyword_nullable(): void
    {
        // Arrange
        $data = [
            'status' => PlanStatus::Published->value,
            'keyword' => '',
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_keyword_max(): void
    {
        // Arrange
        $data = [
            'status' => PlanStatus::Published->value,
            'keyword' => str_repeat('a', 201),
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keyword', $validator->errors()->toArray());
    }
}
