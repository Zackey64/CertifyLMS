<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\MeetingPack;

use App\Http\Requests\MeetingPack\UpdateRequest;
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
            'name' => 'テスト名前',
            'description' => 'テスト説明',
            'meeting_count' => 1,
            'price' => 100,
            'stripe_price_id' => 'abc',
            'sort_order' => 10,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     *  name
     */
    public function test_validation_fails_name_required(): void
    {
        // Arrange
        $data = [
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_name_max(): void
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 101),
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /**
     *  description
     */
    public function test_validation_passes_description_nullable(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_description_max(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'description' => str_repeat('a', 2001),
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /**
     *  meeting_count
     */
    public function test_validation_fails_meeting_count_required(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('meeting_count', $validator->errors()->toArray());
    }

    public function test_validation_fails_meeting_count_max(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 101,
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('meeting_count', $validator->errors()->toArray());
    }

    /**
     *  price
     */
    public function test_validation_fails_price_required(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    public function test_validation_fails_price_max(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 1000001,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    /**
     *  stripe_price_id
     */
    public function test_validation_passes_stripe_price_id_nullable(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     *  sort_order
     */
    public function test_validation_passes_sort_order_nullable(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'meeting_count' => 1,
            'price' => 100,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }
}
