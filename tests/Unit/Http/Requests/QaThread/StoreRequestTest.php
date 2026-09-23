<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\QaThread;

use App\Http\Requests\QaThread\StoreRequest;
use App\Models\Certification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'title' => 'テストタイトル',
            'certification_id' => $certification->id,
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_certification_id_required(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'certification_id' => '',
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('certification_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_certification_id_exists(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'certification_id' => 'invalid-certification-id',
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('certification_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_title_required(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'title' => '',
            'certification_id' => $certification->id,
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_validation_fails_title_max(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'title' => str_repeat('a', 201),
            'certification_id' => $certification->id,
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_validation_fails_body_required(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'title' => 'テストタイトル',
            'certification_id' => $certification->id,
            'body' => '',
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }

    public function test_validation_fails_body_max(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'title' => 'テストタイトル',
            'certification_id' => $certification->id,
            'body' => str_repeat('a', 5001),
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }
}
