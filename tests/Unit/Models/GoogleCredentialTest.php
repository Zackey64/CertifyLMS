<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\GoogleCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GoogleCredentialTest extends TestCase
{
    use RefreshDatabase;

    // user_idカラムのUserモデルへのリレーション（BelongsTo）
    public function test_user_id_relation_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $credential = GoogleCredential::factory()->for($user)->create();
        // Act
        $owner = $credential->user;
        // Assert
        $this->assertTrue($owner->is($user));
    }

    // token_expires_atカラムのキャスト（date）
    public function test_token_expires_at_cast_date(): void
    {
        // Arrange
        $credential = GoogleCredential::factory()->create(['token_expires_at' => '2024-06-01']);
        // Act
        $tokenExpiresAt = $credential->token_expires_at;
        // Assert
        $this->assertInstanceOf(Carbon::class, $tokenExpiresAt);
    }

    // connected_atカラムのキャスト（datetime）
    public function test_connected_at_cast_datetime(): void
    {
        // Arrange
        $credential = GoogleCredential::factory()->create(['connected_at' => '2024-06-01 12:34:56']);
        // Act
        $connectedAt = $credential->connected_at;
        // Assert
        $this->assertInstanceOf(Carbon::class, $connectedAt);
    }
}
