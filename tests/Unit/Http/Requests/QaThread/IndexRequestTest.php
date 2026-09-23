<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\QaThread;

use App\Enums\QaThreadStatus;
use App\Http\Requests\QaThread\IndexRequest;
use App\Models\Certification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'status' => QaThreadStatus::Resolved->value,
            'certification_id' => $certification->id,
            'keyword' => 'テスト',
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_status_nullable(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'status' => '',
            'certification_id' => $certification->id,
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
        $certification = Certification::factory()->create();
        $data = [
            'status' => 'invalid_status',
            'certification_id' => $certification->id,
            'keyword' => 'テスト',
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_passes_certification_id_nullable(): void
    {
        // Arrange
        $data = [
            'status' => QaThreadStatus::Resolved->value,
            'certification_id' => '',
            'keyword' => 'テスト',
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());

    }

    public function test_validation_fails_certification_id_exists(): void
    {
        // Arrange
        $data = [
            'status' => QaThreadStatus::Resolved->value,
            'certification_id' => 'invalidcertificationid',
            'keyword' => 'テスト',
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('certification_id', $validator->errors()->toArray());
    }

    public function test_validation_passes_keyword_nullable(): void
    {
        // Arrange
        $certification = Certification::factory()->create();
        $data = [
            'status' => QaThreadStatus::Resolved->value,
            'certification_id' => $certification->id,
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
        $certification = Certification::factory()->create();
        $data = [
            'status' => QaThreadStatus::Resolved->value,
            'certification_id' => $certification->id,
            'keyword' => str_repeat('a', 201),
        ];
        // Act
        $validator = Validator::make($data, (new IndexRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keyword', $validator->errors()->toArray());
    }
}
