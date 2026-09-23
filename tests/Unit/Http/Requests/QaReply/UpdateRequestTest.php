<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\QaReply;

use App\Http\Requests\QaReply\UpdateRequest;
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
            'body' => 'テスト内容',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_body_required(): void
    {
        // Arrange
        $data = [
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
            'body' => str_repeat('a', 5001),
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }
}
