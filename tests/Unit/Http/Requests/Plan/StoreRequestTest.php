<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Plan;

use App\Http\Requests\Plan\StoreRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'description' => 'テスト説明',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
            'sort_order' => 10,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
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
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_name_max(): void
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 101),
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
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
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_description_max(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'description' => str_repeat('a', 2001),
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /**
     *  duration_days
     */
    public function test_validation_fails_duration_days_required(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'default_meeting_quota' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('duration_days', $validator->errors()->toArray());
    }

    public function test_validation_fails_duration_days_max(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 3651,
            'default_meeting_quota' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('duration_days', $validator->errors()->toArray());
    }

    /**
     *  price
     */
    public function test_validation_fails_default_meeting_quota_required(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('default_meeting_quota', $validator->errors()->toArray());
    }

    public function test_validation_fails_default_meeting_quota_max(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1001,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('default_meeting_quota', $validator->errors()->toArray());
    }

    /**
     *  sort_order
     */
    public function test_validation_passes_sort_order_nullable(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト名前',
            'duration_days' => 1,
            'default_meeting_quota' => 1,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }
}
