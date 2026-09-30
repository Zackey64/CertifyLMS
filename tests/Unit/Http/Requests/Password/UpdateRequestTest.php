<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Password;

use App\Http\Requests\Password\UpdateRequest;
use App\Models\User;
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
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => 'current_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 更新時に現在のパスワードが空だとエラー
    public function test_validation_fails_current_password_required(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => null,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('current_password', $validator->errors()->toArray());
    }

    // 更新時に現在のパスワードが異なるとエラー
    public function test_validation_fails_current_password_mismatch(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => 'wrong_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('current_password', $validator->errors()->toArray());
    }

    // 更新時に新しいパスワードが空だとエラー
    public function test_validation_fails_password_required(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => 'current_password',
            'password' => null,
            'password_confirmation' => 'new_password',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    // 更新時に新しいパスワードが8文字未満だとエラー
    public function test_validation_fails_password_min(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => 'current_password',
            'password' => 'short',
            'password_confirmation' => 'new_password',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    // 更新時に確認用パスワードが空だとエラー
    public function test_validation_fails_password_confirmation_required(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => 'current_password',
            'password' => 'new_password',
            'password_confirmation' => null,
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    // 更新時に確認用パスワードが8文字未満だとエラー
    public function test_validation_fails_password_confirmation_min(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => 'current_password',
            'password' => 'new_password',
            'password_confirmation' => 'short',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    // 更新時に確認用パスワードが新しいパスワードと一致しないとエラー
    public function test_validation_fails_password_mismatch(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => 'current_password',
        ]);
        $this->actingAs($user);
        $data = [
            'current_password' => 'current_password',
            'password' => 'new_password',
            'password_confirmation' => 'different_password',
        ];
        // Act
        $validator = Validator::make($data, (new UpdateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
}
