<?php

declare(strict_types=1);

namespace Tests\Feature\Http\GoogleCalender;

use App\Models\GoogleCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_disconnect_google_calendar(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        GoogleCredential::create([
            'user_id' => $coach->id,
            'google_user_id' => 'google-user-123',
            'calendar_id' => 'primary',
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);
        // Act
        $response = $this->actingAs($coach)->delete(route('settings.google-calendar.destroy'));
        // Assert
        $response->assertRedirect();
        $this->assertDatabaseMissing('google_credentials', [
            'user_id' => $coach->id,
        ]);
    }
}
